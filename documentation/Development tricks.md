# Development tricks

<!-- @import "[TOC]" {cmd="toc" depthFrom=1 depthTo=6 orderedList=false} -->

<!-- code_chunk_output -->

- [Development tricks](#development-tricks)
  - [Flash messages](#flash-messages)
  - [Internationalization](#internationalization)
    - [Translate the strings](#translate-the-strings)
    - [Add a new language](#add-a-new-language)
  - [Authentication](#authentication)
    - [Using policies](#using-policies)
    - [Checking user permissions](#checking-user-permissions)
  - [Tests](#tests)
  - [Choices library](#choices-library)
  - [Datatables](#datatables)

<!-- /code_chunk_output -->

## Flash messages

```php
use Coderello\Laraflash\Facades\Laraflash;

laraflash('text')->success();
```

![Image of flash success](flash_success.png)

```php
use Coderello\Laraflash\Facades\Laraflash;

laraflash('text')->warning();
```

![Image of flash warning](flash_warning.png)

```php
use Coderello\Laraflash\Facades\Laraflash;

laraflash('text')->info();
```

```php
use Coderello\Laraflash\Facades\Laraflash;

laraflash('text')->danger();
```

## Internationalization

All strings in DeepskyLog should be translated. This means that all strings should have the following notation (blade syntax):

```blade
{{ _i("My English text") }}
```

or in php syntax:

```php
_i("My English text")
_i('Translated string with %s', $str);
```

For plural strings, use the _n() function (in blade, where n is the number to use):

```blade
{{ _n('Translated string', 'Translated plural string', $n) }}
```

or in php syntax:

```php
_n('Translated string %s', 'Translated plural string %s', $n, $str);
```

### Translate the strings

Poedit doesn't "understand" blade syntax. When using blade views you must run

```bash
php artisan gettext:update
```

in order to compile all blade views to plain php before updating the translations in Poedit.

Open Poedit and read in the language file to translate (in resources/lang/i18n/LANGUAGE/messages.po). Click on the update catalogue button in POedit to bring in the latest strings to translate.

### Add a new language

- Add the new language to config/laravel-gettext.php and to config/translatable.php

## Authentication

In the Controller (in app/Http/Controllers/), make sure to add 'verified'. This makes sure the user has a verified email address to view the requested page.

```php
$this->middleware(['auth', 'verified'])->except(['show']);
```

OR

In routes/web.php:

```php
Route::resource('lens', 'LensController', ['parameters' => ['lens' => 'lens']])->middleware('verified');
```

### Using policies

To make sure only the correct users can do things:

```bash
php artisan make:policy LensPolicy --model=Lens
```

Add in AuthServiceProvider:

```php
    protected $policies = [
        'App\Models\Lens' => 'App\Policies\LensPolicy'
    ];
```

In LensController (eg edit method):

```php
        $this->authorize('update', $lens);
```

### Checking user permissions

To check if the user is a guest:

In PHP:

```php
Auth::guest()
```

In Blade:

```blade
@guest
    // The user is not authenticated...
@endguest
```

To check if the user is authenticated:

In Blade:

```blade
@auth
    // The user is authenticated...
@endauth
```

To check if the user is administrator:

In PHP:

```php
auth()->user()->isAdmin()
```

In Blade:

```blade
@admin
    // The user is the administrator...
@endadmin
```

The column 'type' in the user table should be set to 'admin' to gain admin privileges.

## Tests

The tests are located in the test directory. They can be executed using:

```bash
phpunit
```

## Choices library

For the dropdown menus, we use choices.js. For selections with multiple inputs, the options should be given as an array.  The selection can be added the following way:

```blade
<x-input.selectmultiple prettyname="modelprettyname" :options="$array" name=recipients[] />
```

For a selection with a single input, the options shoud be given as a string, containing the html code for the options.  The selection can be added the following way:

```blade
<div x-data=''>
    <x-input.select id="quickpickobject" :options="$objects" />
</div>
```

For a selection with a single input using livewire, the options shoud be given as a string, containing the html code for the options.  The selection can be added the following way:

```blade
<div x-data='' wire:ignore>
    <x-input.select-live-wire wire:model="eyepiece" prettyname="myeyepiece" :options="$allEyepieces" selected="('eyepiece')" />
</div>
```

If the value of the selection is not used, the selected values will jump back to the original value.  This can be solved be just showing the selection, for example:

```html
<p hidden>{{ $country }}</p>
```

## Datatables

DeepskyLog uses Livewire datatables. For more information, see https://github.com/MedicOneSystems/livewire-datatables
To make a DataTable model, execute the following command:

```bash
php artisan livewire:datatable LensTable
```

Adapt the app/Http/Livewire/LensTable.php file.

Show the data table using:

```php
    <livewire:lens-table hideable="select" exportable :zoom='$zoomDiameter' :slug='$target->slug' />
```
