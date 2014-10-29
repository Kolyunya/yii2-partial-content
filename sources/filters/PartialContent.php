<?php

namespace kolyunya\yii2\filters;

use Yii;
use yii\web\Controller;
use yii\base\ActionFilter;
use yii\base\Exception;
use kolyunya\yii2\filters\PartialContent\Data;
use kolyunya\yii2\filters\PartialContent\Resource;
use kolyunya\yii2\filters\PartialContent\Stream;
use kolyunya\yii2\filters\PartialContent\File;

class PartialContent extends ActionFilter
{

    public $contentData;

    public $contentResource;

    public $contentStream;

    public $contentFile;

    public $contentType;

    public $contentName;

    private $content;

    private $contentSize;

    private $rangeFrom;

    private $rangeTo;

    private $rangeSize;

    private $rangeWasRequested;

    public function afterAction ( $action , $result )
    {
        $this->initializeContent();
        $this->initializeContentType();
        $this->initializeContentSize();
        $this->initializeDefaultRange();
        $this->parseRequestedRange();
        $this->clearHeaders();
        $this->setCommonHeaders();
        $this->setPartialHeaders();
        $this->sendResponseData();
    }

    private function initializeContent()
    {

        if ( isset($this->contentData) )
        {

            $this->content = new Data($this->contentData);

        }

        else if ( isset($this->contentResource) )
        {

            $this->content = new Resource($this->contentResource);

        }

        else if ( isset($this->contentStream) )
        {

            $this->content = new Stream($this->contentStream);

        }

        else if ( isset($this->contentFile) )
        {

            $this->content = new File($this->contentFile);

        }

        else
        {

            throw new Exception('Invalid filter configuration');

        }

    }

    private function initializeContentType()
    {

        // Set a default value to the "contentType"
        if ( ! isset($this->contentType) )
        {
            $this->contentType = 'application/octet-stream';
        }

    }

    private function initializeContentSize()
    {

        $this->contentSize = $this->content->getSize();

    }

    private function initializeDefaultRange()
    {

        // Default behaviour is to send an entire file
        $this->rangeFrom = 0;
        $this->rangeTo = $this->contentSize - 1;
        $this->rangeSize = $this->contentSize;

    }

    private function parseRequestedRange()
    {

        $rangeRequested = Yii::$app->request->headers->has('Range');
        if ( $rangeRequested === false )
        {
            $this->rangeWasRequested = false;
            return;
        }

        $rangeHeader = Yii::$app->request->headers->get('Range');
        $bytesRangeRequested = preg_match('/^bytes=/',$rangeHeader) !== false;
        if ( $bytesRangeRequested === false )
        {
            $this->rangeWasRequested = false;
            return;
        }

        $this->rangeWasRequested = true;

        $range = str_replace('bytes=','',$rangeHeader);
        $range = explode('-',$range);

        if ( ! empty($range[0]) )
        {
            $this->rangeFrom = $range[0];
        }

        if ( ! empty($range[1]) )
        {
            $this->rangeTo = $range[1];
        }

        $this->rangeSize = $this->rangeTo - $this->rangeFrom + 1;

    }

    private function clearHeaders()
    {

        // Remove headers that might unnecessarily clutter up the output
        Yii::$app->response->headers->remove('Cache-Control');
        Yii::$app->response->headers->remove('Pragma');

    }

    private function setCommonHeaders()
    {

        Yii::$app->response->getHeaders()->set('Accept-Ranges','bytes');
        Yii::$app->response->getHeaders()->set('Content-Type',$this->contentType);
        Yii::$app->response->getHeaders()->set('Content-Length',$this->rangeSize);

        // Set "Content-Disposition" header if the controller set the "contentName" property
        if ( isset($this->contentName) )
        {
            $contentDisposition = 'attachment; filename="' . $this->contentName . '"';
            Yii::$app->response->getHeaders()->set('Content-Disposition',$contentDisposition);
        }

    }

    private function setPartialHeaders()
    {

        // Check if the range was requested
        if ( $this->rangeWasRequested === false )
        {
            return;
        }

        // Set status code #206
        Yii::$app->response->setStatusCode('206');

        // Set "Content-Range" content header
        $contentRangeHeader = "bytes {$this->rangeFrom}-{$this->rangeTo}/{$this->contentSize}";
        Yii::$app->response->getHeaders()->set('Content-Range',$contentRangeHeader);

    }

    private function sendResponseData()
    {

        // Send the contents to the client
        $data = $this->content->getData($this->rangeFrom,$this->rangeSize);
        Yii::$app->getResponse()->content = $data;
        Yii::$app->getResponse()->send();

    }

}