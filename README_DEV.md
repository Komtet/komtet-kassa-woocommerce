# komtet_kassa_woocommerce

## Запуск проекта

* Склонируйте репозиторий включая подмодули для подтягивания SDK - git clone --recurse-submodules
* Скачать установщик WordPress - https://wordpress.org/download/
* Cоздать в корневом каталоге папку php
* Распаковать дистрибутив WordPress из архива в папку php
* Добавить файл .htaccess в папку php со следующим содержимым:
```sh
<FilesMatch "\.md5$">
    Deny from all
</FilesMatch>

DirectoryIndex index.php
Options -Indexes
# Comment the following line, if option Multiviews not allowed here
Options -MultiViews

AddDefaultCharset utf-8

<ifModule mod_rewrite.c>
    RewriteEngine On
    
    # Uncomment the following line, if you are having trouble
    #RewriteBase /

    # if request js file from ROOT
    RewriteCond %{REQUEST_URI} ^\/?[^\/]+\.js$ [or]
    # or if NOT request certain static file from anywhere
    RewriteCond %{REQUEST_URI} !\.(js|css|jpg|jpeg|gif|png|svg|ttf|eot|otf|woff|woff2)$ [or]
    # or if request apple-touch-icon.png icon
    RewriteCond %{REQUEST_URI} apple-touch-icon\.png$ [or]

    # or if other conditions for webdav and caldav are passed
    RewriteCond %{REQUEST_METHOD} ^(POST|PUT|COPY|MOVE|DELETE|PROPFIND|OPTIONS|MKCOL)$ [or]
    RewriteCond %{HTTP:Translate} ^.+$ [or]
    RewriteCond %{HTTP_USER_AGENT} ^(DavClnt|litmus|gvfs|davfs|wdfs|WebDAV|cadaver|Cyberduck)

    # or if file doesnt' exist
    RewriteCond %{REQUEST_FILENAME} !-f
    # or if directory doesnt' exist
    RewriteCond %{REQUEST_FILENAME} !-d

    # dispatch it to index.php
    RewriteRule ^(.*)$ index.php [L,QSA]
</ifModule>

<ifModule mod_headers.c>
    <FilesMatch "\.(jpg|jpeg|png|gif|js|css|svg|ttf|eot|otf|woff|woff2)$">
        Header set Cache-Control "max-age=3153600, public"
    </FilesMatch>
</ifModule>

php_value date.timezone 'Europe/Moscow
```

* Добавить следующую строку в файл wp-config.php для задания прямого способа
* записи в файловую систему при установке плагинов:
```sh
define( 'FS_METHOD', 'direct' );
```

* Запустить сборку проекта
```sh
make build
```

## Установка CMS
* Запустить контейнер
```sh
make start_web7
```

* Проект будет доступен по адресу: localhost:8110;
* Настройки подключения к бд MySQL:
```sh
Сервер: mysql
Пользователь: devuser
Пароль: devpass
БД: test_db
```

* Панель администратора будет доступна по адресу: localhost:8110/wp-admin;

* Перейдите в плагины и поставьте из маркета плагин WooCommerce

## Доступные комманды из Makefile

* Собрать проект
```sh
make build
```

* Запустить проект на php7.2
```sh
make start_web7
```

* Запустить проект на php8.2
```sh
make start_web8
```

* Остановить проект
```sh
make stop
```

* Установить/Обновить модуль в cms
```sh
make update
```

* Собрать архив для установки
```sh
make release
```
