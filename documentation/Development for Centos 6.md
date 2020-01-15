# DeepskyLog development for Centos 6

## Install php 7.3

```
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

# Configuration

```
cd /srw/www/test.deepskylog.org/
php73 /usr/local/bin/composer install
npm install
```

+ Create a new database for deepskylog:
```
mysql create -u root -p
```

and in mysql:
```
create database deepskylogLaravel
```

+ Create .env file from .env.example
```
cp .env.example .env

```
+ Adapt the .env file. Set DB_DATABASE to deepskylogLaravel, DB_USERNAME to root and enter DB_PASSWORD
```
DB_DATABASE=deepskylogLaravel
DB_USERNAME=root
DB_PASSWORD=<PASSWORD>
```
+ Create a new application key
```
php73 artisan key:generate
```
+ Initialize the database:
```
php73 artisan migrate
```

+ Fix permissions
```
chown -R apache:apache /srv/www/test.deepskylog.org
```

+ Create /opt/rh/httpd24/root/etc/httpd/conf.d/test.deepskylog.org.conf

```
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

+ DeepskyLog can be found at https://test.deepskylog.org/

