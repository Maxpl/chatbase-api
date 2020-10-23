Chatbase client
===================================

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

``` bash
composer require maxpl/chatbase-api

add custom repo:

```
  "repositories": [
    {
      "type": "vcs",
      "url": "git@github.com:Maxpl/chatbase-api.git"
    }
  ]

```

require package

```
"require": {
	"maxpl/chatbase-api": "*"
}	
```

First Step
----------

Create account or sign in https://chatbase.com

Add bot
----------

Add you bot and copy his key in config sections 

example:

```php
frontend/config/main.php

'components' => [
	'chatbase' => [
                'class'   => 'Maxpl\ChatbaseApi\ChatbaseClient',
		'api_key' => '0a3edfg345-d123-d56u-d34h-4564564560aa',
                'platform'=> 'telegram',
	]
],
```
Usage
----------
```php
//Send message from user
$chatbase = Yii::$app->chatbase->init()->send([
    'user_id' => $user_id,//required
    'command' => $command or $this->controller->id . '-' . $this->controller->action->id, //optional
    'message' => $message,//required
]);
```

```php
//Send message from bot to user
$chatbase = Yii::$app->chatbase->init()->send([
    'type' => 'agent',
    'user_id' => $user_id,//required
    'message' => $message,//required
]);
```
