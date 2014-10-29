<?php

namespace kolyunya\yii2\filters\PartialContent;

class File extends Resource
{

    public function __construct ( $file )
    {

        // Open the file for read
        $resource = fopen($file,'r');

        // Check if the file was effectively opened
        if ( $resource === false )
        {
            throw new \yii\base\Exception('Unable to open the file');
        }

        // Construct the Resource
        parent::__construct($resource);

    }

}
