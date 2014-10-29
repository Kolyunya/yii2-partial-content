<?php

namespace kolyunya\yii2\filters\PartialContent;

use yii\base\Exception;

class Resource implements IContent
{

    private $resource;

    public function __construct ( $resource )
    {

        $this->resource = $resource;

    }

    public function getSize()
    {

        // Get the resource statistics
        $statistics = fstat($this->resource);

        // Check if resource size was retrieved successfully
        if ( ! isset($statistics['size']) )
        {
            throw new Exception('Unable to retrieve the resource size');
        }

        // Retrieve the resource size
        $size = $statistics['size'];

        return $size;

    }

    public function getData ( $from , $length )
    {

        // Set the resource offset
        $seek = fseek($this->resource,$from);
        if ( $seek !== 0 )
        {
            throw new Exception('Unable to set the resource offset');
        }

        // Read the resource contents
        $data = fread($this->resource,$length);
        if ( $data === false )
        {
            throw new Exception('Unable to read the resource');
        }

        return $data;

    }

}
