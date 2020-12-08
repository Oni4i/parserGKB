<?php

use Illuminate\Support\Facades\Route;
use \App\Helper\Parser\GKB;
use Illuminate\Support\Facades\Storage;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/parse', function () {

    $months_ru = [
        'января' ,
        'февраля' ,
        'марта' ,
        'апреля' ,
        'мая' ,
        'июня' ,
        'июля' ,
        'августа' ,
        'сентября'  ,
        'октября' ,
        'ноября',
        'декабря'
    ];

    $gkb = new GKB();
    $count = 0;

    if (!file_exists(Storage::path('img'))) {
            mkdir(Storage::path('img'));
    }

    foreach ($gkb->getAllArticles() as $article) {
        try {
            $image = file_get_contents($article['image']);
            $savefile = fopen(Storage::path('img') . '\\' . $count . '.png' , 'w');
            $count++;

            fwrite($savefile, $image);
            fclose($savefile);

            $time = explode(',', $article['article_create']);
            list($day, $months) = explode(' ', $time[0]);
            $time = trim($time[1] . '-' . array_search($months, $months_ru) . '-' . $day);
            $article['article_create'] = strtotime($time);

            unset($article['image']);
            $article = new \App\Models\Article($article);
            $article->save();

        } catch (Exception $e) {
            return response()->json(['success' => 0, 'error' => $e->getMessage()]);
        }
    }
    return response()->json(['success' => 1, 'pathToImages' => Storage::path('img')]);
});
