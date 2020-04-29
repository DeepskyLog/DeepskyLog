# DeepskyLog development for Centos 6

## Install php 7.3

```bash
yum install epel-release
rpm -Uvh http://rpms.famillecollet.com/enterprise/remi-release-6.rpm
yum --enablerepo=remi,remi-php73 install php73 php73-php-mbstring php73-php-intl php73-php-pdo php73-php-json php73-php-pear php73-php-gd php73-php-common php73-php-mysqlnd php73-php-process php73-php-opcache php73-php-cli php73-php-zip php73-php-fpm npm php73-php-bcmath php73-php-pecl-imagick
chkconfig php73-php-fpm on
service php73-php-fpm start
wget https://getcomposer.org/composer.phar
chmod +x composer.phar
mv composer.phar /usr/local/bin/composer
curl -sL https://rpm.nodesource.com/setup_11.x | bash -
yum install nodejs
```

## Configuration

```bash
cd /srw/www/test.deepskylog.org/
php73 /usr/local/bin/composer install
npm install
```

+ Create a new database for deepskylog:
  
```bash
mysql create -u root -p
```

and in mysql:

```bash
create database deepskylogLaravel
```

+ Create .env file from .env.example

```bash
cp .env.example .env
```

+ Adapt the .env file. Set DB_DATABASE to deepskylogLaravel, DB_USERNAME to root and enter DB_PASSWORD

```conf
DB_DATABASE=deepskylogLaravel
DB_USERNAME=root
DB_PASSWORD=<PASSWORD>
```

+ Create a new application key

```bash
php73 artisan key:generate
```

+ Initialize the database:

```bash
php73 artisan migrate
```

+ Fix permissions

```bash
chown -R apache:apache /srv/www/test.deepskylog.org
```

+ Create /opt/rh/httpd24/root/etc/httpd/conf.d/test.deepskylog.org.conf

```conf
<VirtualHost IPv4_ADDRESS:80 [IPv6_ADDRESS]:80>
    ...

    <FilesMatch \.(php|argo|skylist|icq|xml|csv|pdf)$>
      SetHandler "proxy:fcgi://127.0.0.1:9000"
    </FilesMatch>

    # DocumentRoot: The directory out of which you will serve your
    # documents. By default, all requests are taken from this directory, but
    # symbolic links and aliases may be used to point to other locations.
    DocumentRoot /srv/www/test.deepskylog.org/public/

    <Directory "/srv/www/test.deepskylog.org/public">
        AllowOverride All
        Order allow,deny
        allow from all
    </Directory>

    ...
</VirtualHost>
```

+ DeepskyLog can be found at [https://test.deepskylog.org/](https://test.deepskylog.org/)

## Seed the database

+ After making an empty database, do a migration to create the tables:

```bash
php73 artisan migrate
```
  
> It's possible that a error is thrown when creating the media table. To fix this, adapt *database/migrations/2019_05_02_093803_create_media_table.php* and change all occurences of **json** to **text**. Rerun the migrate command.

+ Make a link from observers to /observer_pics
  
```bash
ln -s /srv/www/www.deepskylog.org/common/observer_pics/ .
```

+ Set the correct database entries in .env: DB_DATABASE_OLD, DB_USERNAME_OLD, and
DB_PASSWORD_OLD, LIGHTPOLLUTION_KEY, GOOGLEMAPS_KEY
+ Install the dependencies

```bash
php73 /usr/local/bin/composer install
npm install
npm run prod
```

+ Seed the database

```bash
php73 artisan db:seed
```

+ Remove the link to /observer_pics

+ Execute the laravel scheduled jobs (for updating delta t) by adding the following line to crontab:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

+ Don't allow the use of google api from everywhere (also test if the timezone is set correctly then): <https://console.developers.google.com/apis/credentials/key/211?project=deepskylog-1528998866034>
+ Make sure to put `post_max_size = 10M` and `upload_max_filesize = 10M` in /etc/opt/remi/php73/php.ini
+ For homestead, add `client_max_body_size 100M;` to /etc/nginx/nginx.conf

+ Make sure that all callback URLs for logging in using facebook, google, github and twitter are set correctly:
  + https://github.com/organizations/DeepskyLog/settings/applications/1218114
  + https://console.developers.google.com/apis/credentials/oauthclient/675683995449-8n9rrouciqn5mbadcc2dh1048u1nfb2o.apps.googleusercontent.com?project=675683995449
  + https://developers.facebook.com/apps/227386917294563/settings/basic/
  + https://developers.facebook.com/apps/227386917294563/settings/advanced/
