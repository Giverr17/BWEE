<?php

use App\Http\Controllers\Admin\PasswordController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\NewsletterController;
use App\Models\Event;
use App\Models\FaqItem;
use App\Models\Founder;
use App\Models\GalleryItem;
use App\Models\HeroSlide;
use App\Models\Partner;
use App\Models\Program;
use App\Models\RoadmapItem;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $heroSlides = rescue(fn () => HeroSlide::active()->get(), collect());
    $founders = rescue(fn () => Founder::active()->get(), collect());
    $events = rescue(fn () => Event::active()->get(), collect());
    $partners = rescue(fn () => Partner::active()->get(), collect());
    $faqs = rescue(fn () => FaqItem::active()->get(), collect());
    $programs = rescue(fn () => Program::active()->get(), collect());
    $gallery = rescue(fn () => GalleryItem::active()->get(), collect());
    $settings = rescue(fn () => SiteSetting::forLocale(), []);

    return view('welcome', compact(
        'heroSlides',
        'founders',
        'events',
        'partners',
        'faqs',
        'programs',
        'gallery',
        'settings'
    ));
})->name('home');

Route::get('/about', function () {
    $faqs = rescue(fn () => FaqItem::active()->get(), collect());
    $settings = rescue(fn () => SiteSetting::forLocale(), []);

    return view('pages.about', compact('faqs', 'settings'));
})->name('about');

Route::get('/founders', function () {
    $founders = rescue(fn () => Founder::active()->get(), collect());
    $settings = rescue(fn () => SiteSetting::forLocale(), []);

    return view('pages.founders', compact('founders', 'settings'));
})->name('founders');

Route::get('/programs', function () {
    $programs = rescue(fn () => Program::active()->get(), collect());
    $roadmapItems = rescue(fn () => RoadmapItem::active()->get(), collect());
    $settings = rescue(fn () => SiteSetting::forLocale(), []);

    return view('pages.programs', compact('programs', 'roadmapItems', 'settings'));
})->name('programs');

Route::get('/events', function () {
    $events = rescue(fn () => Event::active()->get(), collect());
    $settings = rescue(fn () => SiteSetting::forLocale(), []);

    return view('pages.events', compact('events', 'settings'));
})->name('events');

Route::get('/gallery', function () {
    $gallery = rescue(fn () => GalleryItem::active()->get(), collect());
    $settings = rescue(fn () => SiteSetting::forLocale(), []);

    return view('pages.gallery', compact('gallery', 'settings'));
})->name('gallery');

Route::get('/partners', function () {
    $partners = rescue(fn () => Partner::active()->get(), collect());
    $settings = rescue(fn () => SiteSetting::forLocale(), []);

    return view('pages.partners', compact('partners', 'settings'));
})->name('partners');

Route::get('/get-involved', function () {
    $settings = rescue(fn () => SiteSetting::forLocale(), []);

    return view('pages.involved', compact('settings'));
})->name('involved');

Route::get('/contact', function () {
    $settings = rescue(fn () => SiteSetting::forLocale(), []);

    return view('pages.contact', compact('settings'));
})->name('contact');

Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

Route::post('/newsletter', [NewsletterController::class, 'store'])->name('newsletter.store');

Route::get('/lang/{locale}', function (string $locale) {
    abort_unless(in_array($locale, config('app.available_locales'), true), 404);
    session(['locale' => $locale]);

    return redirect()->back();
})->name('locale.set');

Route::prefix('switch')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [AdminLoginController::class, 'create'])->name('login');
        Route::post('login', [AdminLoginController::class, 'store']);
    });

    Route::get('password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::middleware('auth')->group(function () {
        Route::post('logout', [AdminLoginController::class, 'destroy'])->name('logout');

        Route::get('/', function () {
            return view('admin.dashboard');
        })->name('dashboard');
        Route::get('faqs', fn () => view('admin.faqs'))->name('faqs');
        Route::get('partners', fn () => view('admin.partners'))->name('partners');
        Route::get('programs', fn () => view('admin.programs'))->name('programs');
        Route::get('roadmap', fn () => view('admin.roadmap'))->name('roadmap');
        Route::get('founders', fn () => view('admin.founders'))->name('founders');
        Route::get('events', fn () => view('admin.events'))->name('events');
        Route::get('gallery', fn () => view('admin.gallery'))->name('gallery');
        Route::get('messages', fn () => view('admin.messages'))->name('messages');
        Route::get('subscribers', fn () => view('admin.subscribers'))->name('subscribers');
        Route::get('settings', fn () => view('admin.settings'))->name('settings');
        Route::get('newsletter', fn () => view('admin.newsletter'))->name('newsletter');
    });
});
