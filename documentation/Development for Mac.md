# DeepskyLog development for Mac

## Install brew

+ Install homebrew
```
mkdir brew
cd brew
git clone --depth=1 https://github.com/Homebrew/brew ~/.brew
export PATH=$PATH:~/brew/bin/
```
+ Add the following line to ~/.bash_profile to use the brew path
```
export PATH=$PATH:~/brew/bin/
```
+ Install laravel dependencies using homebrew
```
brew install php
brew install composer
brew install mariadb
brew services start mariadb
```

## Install GitHub Desktop

+ Download from https://desktop.github.com/
+ Install GitHub Desktop

## Install Visual Studio Code

+ Download from https://code.visualstudio.com/Download
+ Install Visual Studio

It is also possible to install any other IDE. Other options are eclipse and atom.

# Configuration

```
git clone https://github.com/DeepskyLog/DeepskyLog.laravel
cd DeepskyLog.laravel
composer install
npm install
```

+ Create a new database with name 'deepskylog'.
```
mysql -u root
```

and in mysql:

```
create database deepskylog;
```

+ Create .env file from .env.example
```
cp .env.example .env
```
+ Adapt the .env file. Set DB_DATABASE to deepskylog, DB_USERNAME to root and leave DB_PASSWORD
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

+ Serve the website
```
php artisan serve
```
+ DeepskyLog can be found at http://localhost:8000/
