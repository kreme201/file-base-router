# File Based Router for PHP

The File Based Router library is designed to handle dynamic routing based on files.

## Install

```
composer require kreme201/file-base-router
```

It has been developed and tested on PHP 8.3.

### Apache Setting

```
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ /index.php [L]
```

## Usage

Project Structure

```
.
├── pages
│   ├── board
│   │   ├── [id].php
│   │   └── index.php
│   ├── index.php
├── public
│   └── index.php
└── vendor
```

public/index.php

```php
<?php
require '/path/to/vendor/autoload.php';

$dispatcher = new Kreme201\FileBaseRouter\Dispatcher(dirname(__DIR__) . '/pages');
$template = $dispatcher->dispatch(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

if (false !== $template && file_exists($template)) {
    include $template;
} else {
    http_response_code(404);
}
```

When creating an instance of the ```Dispatcher```, routing is handled based on the provided path.  
Dynamic paths can be processed using ```[{name}]``` and ```[...{name}]``` to capture dynamic data.  
Each data can be accessed via ```$_GET```, and data defined with ```[...{name}]``` includes all sub-paths.

```
/: {base_path}/index.php
/board: {base_path}/board/index.php
/board/123: {base_path}/board/[id].php, $_GET['id'] => 123
/test/slug/example: {base_path}/test/[...slug].php, $_GET['slug'] => 'slug/example'
```

## Example

[File Base Router Example](https://github.com/kreme201/file-base-router-example)
