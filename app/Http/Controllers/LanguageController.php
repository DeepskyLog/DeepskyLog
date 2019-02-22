<?php

namespace App\Http\Controllers;

use App\User;
use App\Http\Controllers\Controller;
use Redirect;
use URL;
use Xinax\LaravelGettext\Facades\LaravelGettext;

class LanguageController extends Controller
{
    /**
     * Changes the current language and returns to previous page
     * @return Redirect
     */
    public function changeLang()
    {
        LaravelGettext::setLocale(request('language'));
        return Redirect::to(URL::previous());
    }
}
?>
