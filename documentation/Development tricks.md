# Development tricks

## Flash messages

```
flash()->success('text');
```

![Image of flash success](flash_success.png)

```
flash()->warning('text');
```

![Image of flash warning](flash_warning.png)

```
flash()->error('text');
```

## Internationalization

All strings in DeepskyLog should be translated. This means that all strings should have the following notation (blade syntax):

```
{{ _i("My English text") }}
```

or in php syntax:

```
_i("My English text")
_i('Translated string with %s', $str);
```

For plural strings, use the _n() function (in blade, where n is the number to use):

```
{{ _n('Translated string', 'Translated plural string', $n) }}
```

or in php syntax:

```
_n('Translated string %s', 'Translated plural string %s', $n, $str);
```

### Translate the strings

Poedit doesn't "understand" blade syntax. When using blade views you must run

```
php artisan gettext:update
```

in order to compile all blade views to plain php before update the translations in Poedit.

Open Poedit and read in the language file to translate (in resources/lang/i18n/LANGUAGE/messages.po). Click on the update catalogue button in POedit to bring in the latest strings to translate.

## Authentication


In the Controller (in app/Http/Controllers/), make sure to add 'verified'. This makes sure the user has a verified email address.
```
$this->middleware(['auth', 'verified', 'clearance'])->except(['show']);
```

OR 

In routes/web.php:

```
Route::resource('lens', 'LensController', ['parameters' => ['lens' => 'lens']])->middleware('verified');
```

## Mails
