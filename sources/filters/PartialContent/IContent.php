<?php

namespace kolyunya\yii2\filters\PartialContent;

interface IContent
{

    public function getSize();

    public function getData($from, $length);
}
