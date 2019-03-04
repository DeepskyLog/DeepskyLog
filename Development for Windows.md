# DeepskyLog development for windows

## Install laragon

+ Download the Laragon Full edition from https://laragon.org/download/
+ Install laragon (use the standard settings)

## Install GitHub Desktop

+ Download from https://desktop.github.com/
+ Install GitHub Desktop

## Install Visual Studio Code

+ Download from https://code.visualstudio.com/Download
+ Install Visual Studio

It is also possible to install any other IDE. Other options are eclipse and atom.

# Configuration

+ Start laragon
+ Click on 'Start all'
+ A Firewall Alert will pop up for Apache HTTP server and for mysqld. Make sure to allow access to these services.

+ In laragon, click on 'Terminal'.
```
git clone https://github.com/DeepskyLog/DeepskyLog.laravel
cd DeepskyLog.laravel
composer install
npm install
```

+ In laragon, click on 'Menu'->'MySQL'->'Create database'. Create a new database with name 'deepskylog'.
+ Create .env file from .env.example
```
cp .env.example .env
```
+ Adapt the .env file. Set DB_DATABASE to deepskylog, DB_USERNAME to root and leave DB_PASSWORD empty:
```
DB_DATABASE=deepskylog
DB_USERNAME=root
DB_PASSWORD=
```
+ Create a new application key
```
php artisan key:generate
```
+ Initialize the database:
```
php artisan migrate
```
+ In laragon, select 'Menu'->'Apache'->'Reload'
+ DeepskyLog can be found at http://deepskylog.laravel.test/
