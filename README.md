A Simple AppNexus API client for yii2
=====================================

This extension provides the [AppNexus API client](https://github.com/f3ath/AppNexusClient) integration for the Yii2 framework.


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist fedemotta/yii2-appnexusapiclient "*"
```

or add

```
"fedemotta/yii2-appnexusapiclient": "*"
```

to the require section of your `composer.json` file.


General Usage
-------------

To use this extension, simply add the following code in your application configuration:

```php
return [
    //....
    'components' => [
        'appnexusapiclient' => [
            'class' => 'fedemotta/appnexusapiclient/AppNexusApiClient',
            'username' => 'yourusername',
            'password' => 'yourpassword',
            'host' => 'http://api-console.client-testing.adnxs.net/', //or http:://api.appnexus.com
            'storage_type' => 'Apc', //available token storage are Array, Apc and Memcached
            'storage_type_settings' => ['prefix_',0], //specifies the storage type settings
        ],
    ],
];
```


Getting users from AppNexus API is very simple:

```php
$users = Yii::$app->appnexusapiclient->get('/user');
```