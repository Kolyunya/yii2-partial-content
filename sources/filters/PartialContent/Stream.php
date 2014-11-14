<?php

namespace kolyunya\yii2\filters\PartialContent;

use yii\base\Exception;

class Stream extends Data
{

    public function __construct($stream)
    {

        // Get the stream contents
        $data = stream_get_contents($stream);

        // Check if the data was retrieved successfully
        if ($data === false) {
            throw new Exeption('Could not get the stream contents');
        }

        parent::__construct($data);

    }
}
