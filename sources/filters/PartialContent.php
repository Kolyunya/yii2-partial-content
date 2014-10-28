<?php

namespace kolyunya\yii2\filters;

use Yii;
use yii\web\Controller;
use yii\base\ActionFilter;

class PartialContent extends ActionFilter
{

    private $contentType;

    private $contentSize;

    private $rangeFrom;

    private $rangeTo;

    private $rangeSize;

    private $rangeWasRequested;

    public function afterAction ( $action , $result )
    {
        $this->initializeContentType();
        $this->initializeContentSize();
        $this->initializeDefaultRange();
        $this->parseRequestedRange();
        $this->clearHeaders();
        $this->setCommonHeaders();
        $this->setPartialHeaders();
        $this->sendResponseData();
    }

    private function initializeContentType()
    {

        // If the controller set "contentType" then use it
        if ( isset($this->owner->contentType) )
        {
            $this->contentType = $this->owner->contentType;
        }

        // Else use a default value of "application/octet-stream"
        else
        {
            $this->contentType = 'application/octet-stream';
        }

    }

    private function initializeContentSize()
    {

        // If a controller specified a "content"
        if ( isset($this->owner->content) )
        {

            // Calculate the content size directly
            $this->contentSize = strlen($this->owner->content);

        }

        // If a controller specified a "resource"
        else
        {

            // Then use "fstat" to calculate it's size
            $contentStatistics = fstat($this->owner->resource);
            $this->contentSize = $contentStatistics['size'];

        }

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
        if ( isset($this->owner->contentName) )
        {
            $contentDisposition = 'attachment; filename="' . $this->owner->contentName . '"';
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

        // Data to be sent to the client
        $responseData;

        // If a controller specified a "content"
        if ( isset($this->owner->content) )
        {

            $responseData = substr($this->owner->content,$this->rangeFrom,$this->rangeSize);

        }

        // If a controller specified a "resource"
        else
        {

            // Set the file cursor
            $seek = fseek($this->owner->resource,$this->rangeFrom);
            if ( $seek !== 0 )
            {
                throw new \yii\base\Exception();
            }

            // Read the file contents
            $responseData = fread($this->owner->resource,$this->rangeSize);
            if ( $responseData === false )
            {
                throw new \yii\base\Exception();
            }

        }

        // Send the contents to the client
        Yii::$app->getResponse()->content = $responseData;
        Yii::$app->getResponse()->send();

    }

}