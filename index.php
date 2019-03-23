index.php
<?php

use framework\http\Request;

chdir(dirname(__DIR__));
require 'vendor/autoload.php';

/** Initialization */
//$request = new Request($_GET, $_POST);
/*$request = new Request();

$request->setQueryParams($_GET);
$request->setParsedBody($_POST);*/

/*$request = (new Request())
    ->setQueryParams($_GET)
    ->setParsedBody($_POST);*/

$request = RequestFactory::fromGlobals();

/** Action */
$name = $request->getQueryParams()['name'];

if (empty($name)) {
    $name = 'Guest';
}
header('X-developer: Strelkov');

echo "Hello, $name!";

Request.php
<?php
/**
 * Class Request
 *
 * Work with requests
 *
 * @author Strelkov Timur
 */

namespace framework\http;

/**
 * Class Request
 */
class Request
{
    private $queryParams;
    private $parsedBody;

    /*public function __construct(array $queryParams, $parsedBody = null)
    {
        $this->queryParams = $queryParams;
        $this->parsedBody = $parsedBody;
    }*/

    public function getQueryParams()
    {
        return $this->queryParams;
    }

    public function setQueryParams(array $query)
    {
        $new = clone $this;
        $new->queryParams = $query;
        return $new;
    }

    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    public function setParsedBody($data)
    {
        $new = clone $this;
        $new->parsedBody = $data;
        return $new;
    }
}


RequestFactory.php
<?php

namespace framework\http;

use framework\http\Request;

/**
 * Class RequestFactory
 */
class RequestFactory
{
    public static function fromGlobals(array $query = null, array $body = null)
    {
        return (new Request())
            ->setQueryParams($query ?: $_GET)
            ->setQueryParams($body ?: $_POST);
    }
}



nginx.conf
server {
    charset utf-8;
    client_max_body_size 128M;

    listen 80;

    #server_name mysite.local;
    root        /var/www/container/public;
    index       index.php;

    #access_log  /path/to/basic/log/access.log;
    #error_log   /path/to/basic/log/error.log;

    location / {
        # Redirect everything that isn't a real file to index.php
        try_files $uri $uri/ /index.php$is_args$args;
    }

    # 404
    #error_page 404 /404.html;

    # deny accessing php files for the /assets directory
    location ~ ^/assets/.*\.php$ {
        deny all;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_pass unix:/var/run/php-fpm.sock;
        try_files $uri =404;
    }

    location ~* /\. {
        deny all;
    }
}



docker-compose.yml
version: "3.5"

#networks:
#  specific-network-name:
#    external: true

services:

  web:
    container_name: container-php
    image: richarvey/nginx-php-fpm:latest
    restart: always
    #networks:
    #  - default
    depends_on:
      - mysql
    links:
      - mysql
    #external_links:
    #  - container-rabbit
    volumes:
      - ./container:/var/www/container
      - ./nginxdata/nginx.conf:/etc/nginx/sites-enabled/default.conf
      - ./phpdata:/usr/local/etc/php
    ports:
      - "7750:80"

  mysql:
    container_name: container-db
    image: mysql:5.7
    restart: always
    environment:
      MYSQL_ROOT_PASSWORD: 123
      MYSQL_DATABASE: framework
    #networks:
    #  - default
    #  - specific-network-name
    volumes:
      - ./mysqldata:/var/lib/mysql
    ports:
      - "7760:3306"
      
      
 composer.json
 {
  "config": {
    "sort-packages": true
  },
  "require": {
    "roave/security-advisories": "dev-master"
  },
  "autoload": {
    "psr-4": {
      "framework\\": "src/framework"
    }
  }
}

