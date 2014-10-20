#Yii2 partial content filter

##Description

A [Yii2 filter](http://www.yiiframework.com/doc-2.0/yii-base-actionfilter.html) which empowers a controller with a functionality of a [partial HTTP response](https://ru.wikipedia.org/wiki/%D0%A1%D0%BF%D0%B8%D1%81%D0%BE%D0%BA_%D0%BA%D0%BE%D0%B4%D0%BE%D0%B2_%D1%81%D0%BE%D1%81%D1%82%D0%BE%D1%8F%D0%BD%D0%B8%D1%8F_HTTP#206).

The widget is [composer](https://getcomposer.org/)-enabled. You can aquire the latest available version from the [packagist repository](https://packagist.org/packages/kolyunya/yii2-partial-content).

##Usage example

To use the filter your controller must set just one public property: `content`. The `content` then will be sent to the client by the filter completly or partially depending on the request. The filter should be added just like any other Yii2 action filter. You may also specify actions which should be processed by the filter.

~~~php
public function behaviors()
{
    return
    [
        [
            'class' => 'kolyunya\yii2\filters\PartialContent',
            'only' =>
            [
                'get'
            ]
        ]
    ];
}
~~~

The controller may also set the public `contentType` property which will be used by the filter as a value of the `Content-Type` header. The default value is `application/octet-stream`.

The controller may also set the public `contentName` property which will be used as a filename value of the `Content-Disposition` header. If the controller does not set this property this header will not be sent.
