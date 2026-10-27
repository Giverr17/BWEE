<?php

use App\Http\Controllers\Admin\PasswordController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\EventRegistrationController;
use App\Http\Controllers\NewsletterController;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\FaqItem;
use App\Models\Founder;
use App\Models\GalleryItem;
use App\Models\HeroSlide;
use App\Models\Partner;
use App\Models\Program;
use App\Models\RoadmapItem;
use App\Models\SiteSetting;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
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

Route::get('/register/{event:slug}', [EventRegistrationController::class, 'create'])->name('events.register');
Route::post('/register/{event:slug}', [EventRegistrationController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('events.register.store');
Route::get('/register/{event:slug}/thanks', [EventRegistrationController::class, 'thanks'])->name('events.register.thanks');

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
        Route::get('events/{event}/qr-svg', function (Event $event, QrCodeService $qrService) {
            abort_unless($event->slug, 404, 'Event has no slug.');
            $url = route('events.register', $event);
            $svg = $qrService->generateSvg($url);
            $filename = "{$event->slug}-qr.svg";

            return response($svg, 200, [
                'Content-Type' => 'image/svg+xml',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        })->name('events.qr-svg');
        Route::get('gallery', fn () => view('admin.gallery'))->name('gallery');
        Route::get('messages', fn () => view('admin.messages'))->name('messages');
        Route::get('registrations', fn () => view('admin.registrations'))->name('registrations');
        Route::get('registrations/export-csv/{event}', function (Event $event, Request $request) {
            $search = $request->query('search', '');
            $registrations = EventRegistration::where('event_id', $event->id)
                ->when($search, function ($query) use ($search) {
                    $term = '%'.trim($search).'%';
                    $query->where(function ($q) use ($term) {
                        $q->where('full_name', 'like', $term)
                            ->orWhere('email', 'like', $term)
                            ->orWhere('profession', 'like', $term)
                            ->orWhere('organization', 'like', $term);
                    });
                })
                ->orderBy('created_at', 'desc')
                ->get();

            $slugPart = $event->slug ?: ('event-'.$event->id);
            $filename = "{$slugPart}-registrations.csv";

            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            return response()->streamDownload(function () use ($registrations) {
                $handle = fopen('php://output', 'w');
                fwrite($handle, "\xEF\xBB\xBF");
                fputcsv($handle, [
                    'ID',
                    'Full Name',
                    'Email',
                    'Phone',
                    'Country of Residence',
                    'Country Code',
                    'Nationality',
                    'Nationality Code',
                    'Profession / Area of Interest',
                    'Organization',
                    'Expectations',
                    'Consent Timestamp',
                    'Locale',
                    'Registered At',
                ]);
                foreach ($registrations as $reg) {
                    fputcsv($handle, [
                        $reg->id,
                        $reg->full_name,
                        $reg->email,
                        $reg->phone,
                        $reg->country_of_residence_name_en,
                        $reg->country_of_residence,
                        $reg->nationality_name_en,
                        $reg->nationality,
                        $reg->profession,
                        $reg->organization ?: '',
                        $reg->expectations,
                        $reg->consent_at ? $reg->consent_at->toIso8601String() : '',
                        $reg->locale,
                        $reg->created_at ? $reg->created_at->format('Y-m-d H:i:s') : '',
                    ]);
                }
                fclose($handle);
            }, $filename, $headers);
        })->name('registrations.export-csv');
        Route::get('subscribers', fn () => view('admin.subscribers'))->name('subscribers');
        Route::get('settings', fn () => view('admin.settings'))->name('settings');
        Route::get('newsletter', fn () => view('admin.newsletter'))->name('newsletter');
    });
});
