<?php

use App\Modules\SEO\Http\Controllers\ContactController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/robots.txt', function () {
    $disallow = app()->environment('production') ? '' : 'Disallow: /';

    return response("# www.robotstxt.org/\nUser-agent: *\n{$disallow}\nSitemap: ".asset('storage/sitemap.xml')."\n", 200, [
        'Content-Type' => 'text/plain',
    ]);
})->name('robots.txt');

Route::view('/about', 'modules.seo.static.about')->name('about');
Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'submit']);
Route::view('/privacy', 'modules.seo.static.privacy')->name('privacy');
Route::view('/terms', 'modules.seo.static.terms')->name('terms');
