<?php

namespace kolyunya\yii2\filters\partial_content;

class Data implements IContent
{

    private $data;

    public function __construct ( $data )
    {

        $this->data = $data;

    }

    public function getSize()
    {

        $size = strlen($this->data);
        return $size;

    }

    public function getData ( $from , $length )
    {

        $data = substr($this->data,$from,$length);
        return $data;

    }

}
