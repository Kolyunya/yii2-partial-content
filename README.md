#Yii2 partial content filter

##Description

A [Yii2 filter](http://www.yiiframework.com/doc-2.0/yii-base-actionfilter.html) which empowers a controller with a functionality of a [partial HTTP response](https://ru.wikipedia.org/wiki/%D0%A1%D0%BF%D0%B8%D1%81%D0%BE%D0%BA_%D0%BA%D0%BE%D0%B4%D0%BE%D0%B2_%D1%81%D0%BE%D1%81%D1%82%D0%BE%D1%8F%D0%BD%D0%B8%D1%8F_HTTP#206).

The widget is [composer](https://getcomposer.org/)-enabled. You can aquire the latest available version from the [packagist repository](https://packagist.org/packages/kolyunya/yii2-partial-content).

##Usage example

To use the filter your controller must set either `contentData` or `contentResource` property of the filter. Those properties then will be used by the filter to send data to the client completly or partially depending on it's request. The filter should be added to the controller just like any other Yii2 action filter. You may also specify actions which should be processed by the filter.

The controller may also set the `contentType` property of the filter which will be used by the filter as a value of the `Content-Type` header. The default value is `application/octet-stream`.

The controller may also set the `contentName` property of the filter which will be used as a filename value of the `Content-Disposition` header. If the controller does not set this property this header will not be sent.

~~~php

public function behaviors()
{

    return
    [

        // Add a partial content filter
        'partial-content' =>
        [

            // Specify filter class name
            'class' => 'kolyunya\yii2\filters\PartialContent',

            // Specify which actions it will be applied to
            'only' =>
            [
                'get'
            ]

        ]

    ];

}

public function actionGet()
{

    // Get a reference to the filter
    $behavior = $this->getBehavior('partial-content');

    // Either set the data itself
    $behavior->contentData = $this->data;

    // Or specify the data resource
    $behavior->contentResource = $this->resource;

    // Optionally set the content type
    $behavior->contentType = 'audio/mpeg';

    // Optionally set the content name
    $behavior->contentName = 'My new song';

    // The filter will do the rest itself

}

~~~
