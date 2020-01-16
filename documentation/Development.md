# DeepskyLog development

## Install GitHub Desktop

+ Download from https://desktop.github.com/
+ Install GitHub Desktop

## Install Visual Studio Code

+ Download from https://code.visualstudio.com/Download
+ Install Visual Studio

It is also possible to install any other IDE. Other options are eclipse and atom.

## Check out the source code

+ Before checking out, you should [fork the DeepskyLog repository on GitHub](https://github.com/DeepskyLog/DeepskyLog). Click on **Fork**.
+ In your local fork, switch to the *laravel* branch and click *Clone or Download*. You can automatically start GitHub Desktop to check the code out. Make sure you checked out the **laravel** branch.

## Install homestead

+ See the [laravel homestead](https://laravel.com/docs/6.x/homestead#installation-and-setup) page for installation instructions. You will need to install [Virtualbox](https://www.virtualbox.org/wiki/Downloads) and [Vagrant](https://www.vagrantup.com/docs/installation/) first.
+ Map your checkout out code to */home/vagrant/code*.
+ Example Homestead.yaml file:

```yaml
folders:
    - map: ~/GitHub/DeepskyLog
      to: /home/vagrant/code

sites:
    - map: deepskylog.test
      to: /home/vagrant/code/public

databases:
    - deepskylog

features:
    - mariadb: true
    - ohmyzsh: false
    - webdriver: false
```

+ Create .env file from .env.example

```bash
cp .env.example .env
```

+ Adapt the .env file. Set DB_DATABASE to deepskylog, DB_USERNAME to root and leave DB_PASSWORD

```bash
DB_DATABASE=deepskylog
DB_USERNAME=root
DB_PASSWORD=
```

+ Start Homestead

```bash
homestead up
```

+ Log in into Homestead

```bash
homestead ssh
```

+ Create a new application key.

```bash
cd code
php artisan key:generate
```

+ Initialize the database:

```bash
php artisan migrate
```

+ Seed the database:

```bash
php artisan db:seed
```

+ DeepskyLog can be found at http://deepskylog.test/
