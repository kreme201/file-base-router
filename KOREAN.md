# File Based Router for PHP

File Based Router 라이브러리는 파일 기반으로 동적 라우팅을 처리하기 위해 작성되었습니다.

## Install

```
composer require kreme201/file-base-router
```

PHP 8.3 에서 작성 및 테스트 되었습니다.

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

```Dispatcher``` 인스턴스 생성시 전달된 경로를 기반으로 라우팅이 처리되며,  
동적 경로는 ```[{name}]```, ```[...{name}]``` 으로 동적 데이터를 처리할 수 있습니다.  
각 데이터는 ```$_GET``` 으로 조회 가능하며, ```[...{name}]``` 으로 정의된 데이터는 모든 하위 경로를 포함합니다.

```
/: {base_path}/index.php
/board: {base_path}/board/index.php
/board/123: {base_path}/board/[id].php, $_GET['id'] => 123
/test/slug/example: {base_path}/test/[...slug].php, $_GET['slug'] => 'slug/example'
```

## Example

[File Base Router Example](https://github.com/kreme201/file-base-router-example)
