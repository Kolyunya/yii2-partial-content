<?php

namespace kolyunya\yii2\filters\partial_content;

interface IContent
{

    public function getSize();

    public function getData ( $from , $to );

}
