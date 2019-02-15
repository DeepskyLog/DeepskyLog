<?php

namespace App\Http\Controllers;

use App\User;
use App\Http\Controllers\Controller;
use Redirect;
use URL;
use Xinax\LaravelGettext\Facades\LaravelGettext;

class HomeController extends Controller
{
    /**
     * Changes the current language and returns to previous page
     * @return Redirect
     */
    public function changeLang($locale=null)
    {
        LaravelGettext::setLocale($locale);
        return Redirect::to(URL::previous());
    }
}
?>
