<?php

use App\Mail\AdminComposedMail;
use App\Mail\NewMessageNotification;
use App\Mail\RegistrationConfirmation;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\NewsletterSubscriber;
use App\Models\SiteSetting;
use App\Services\MailLogService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

uses(DatabaseTransactions::class);

it('queues a confirmation email when a registration is submitted', function () {
    Mail::fake();

    $event = Event::create([
        'title' => ['en' => 'Test Summit EN', 'fr' => 'Test Sommet FR'],
        'event_date' => Carbon::tomorrow()->format('Y-m-d'),
        'registration_open' => true,
    ]);

    $response = $this->post('/register/'.$event->slug, [
        'full_name' => 'Aisha Test',
        'email' => 'aisha@mailtest.com',
        'phone' => '+1 416 555 0100',
        'country_of_residence' => 'CA',
        'nationality' => 'NG',
        'profession' => 'Engineer',
        'expectations' => 'Testing the mail system.',
        'consent' => '1',
    ]);

    $response->assertRedirect(route('events.register.thanks', $event));

    Mail::assertQueued(RegistrationConfirmation::class, function ($mail) {
        return $mail->hasTo('aisha@mailtest.com');
    });
});

it('sends the confirmation email with the correct From and Reply-To and has plain text part', function () {
    Mail::fake();

    config([
        'mail.from.address' => 'blackwomenenterpriseexchange@gmail.com',
        'mail.from.name' => "Black Women's Enterprise Exchange",
        'mail.reply_to.address' => 'info@bweeofficial.com',
        'mail.reply_to.name' => "Black Women's Enterprise Exchange",
    ]);

    $event = Event::create([
        'title' => ['en' => 'Reply-To Test Event'],
        'event_date' => Carbon::tomorrow()->format('Y-m-d'),
        'registration_open' => true,
    ]);

    $this->post('/register/'.$event->slug, [
        'full_name' => 'Clara Test',
        'email' => 'clara@mailtest.com',
        'phone' => '+1 514 555 0101',
        'country_of_residence' => 'CA',
        'nationality' => 'FR',
        'profession' => 'Consultant',
        'expectations' => 'Networking and learning.',
        'consent' => '1',
    ]);

    Mail::assertQueued(RegistrationConfirmation::class, function ($mail) {
        $mail->build();

        return $mail->hasTo('clara@mailtest.com')
            && $mail->hasFrom('blackwomenenterpriseexchange@gmail.com')
            && $mail->hasReplyTo('info@bweeofficial.com')
            && ! empty($mail->textView);
    });
});

it('sends the confirmation email in English when registration locale is en', function () {
    Mail::fake();
    app()->setLocale('en');

    $event = Event::create([
        'title' => ['en' => 'Language Event EN'],
        'event_date' => Carbon::tomorrow()->format('Y-m-d'),
        'registration_open' => true,
    ]);

    $this->post('/register/'.$event->slug, [
        'full_name' => 'English User',
        'email' => 'english@mailtest.com',
        'phone' => '+1 416 555 0102',
        'country_of_residence' => 'CA',
        'nationality' => 'CA',
        'profession' => 'Developer',
        'expectations' => 'English language test.',
        'consent' => '1',
    ]);

    Mail::assertQueued(RegistrationConfirmation::class, function ($mail) {
        $mail->build();

        return str_contains($mail->subject ?? '', 'Registration confirmed');
    });
});

it('sends the confirmation email in French when registration locale is fr', function () {
    Mail::fake();
    app()->setLocale('fr');

    $event = Event::create([
        'title' => ['en' => 'Language Event FR', 'fr' => 'Sommet FR'],
        'event_date' => Carbon::tomorrow()->format('Y-m-d'),
        'registration_open' => true,
    ]);

    $this->withSession(['locale' => 'fr'])->post('/register/'.$event->slug, [
        'full_name' => 'Utilisatrice Francaise',
        'email' => 'french@mailtest.com',
        'phone' => '+1 514 555 0103',
        'country_of_residence' => 'CA',
        'nationality' => 'FR',
        'profession' => 'Consultante',
        'expectations' => 'Test en langue francaise.',
        'consent' => '1',
    ]);

    Mail::assertQueued(RegistrationConfirmation::class, function ($mail) {
        $mail->build();

        return str_contains($mail->subject ?? '', "Confirmation d'inscription");
    });

    app()->setLocale('en');
});

it('does not fail or return 500 when the mail system throws an exception', function () {
    Mail::shouldReceive('to')->andThrow(new Exception('SMTP connection refused'));

    $event = Event::create([
        'title' => ['en' => 'Mail Failure Event'],
        'event_date' => Carbon::tomorrow()->format('Y-m-d'),
        'registration_open' => true,
    ]);

    $response = $this->post('/register/'.$event->slug, [
        'full_name' => 'Fail Test',
        'email' => 'fail@mailtest.com',
        'phone' => '+1 416 555 0199',
        'country_of_residence' => 'CA',
        'nationality' => 'CA',
        'profession' => 'Tester',
        'expectations' => 'Testing mail failure resilience.',
        'consent' => '1',
    ]);

    $response->assertRedirect(route('events.register.thanks', $event));
    expect(EventRegistration::where('email', 'fail@mailtest.com')->exists())->toBeTrue();
});

it('queues a contact alert to every address in contact_notification_emails with Reply-To set to the visitor', function () {
    Mail::fake();

    SiteSetting::set('contact_notification_emails', 'admin1@example.com,admin2@example.com');

    $this->post('/contact', [
        'name' => 'Visitor Name',
        'email' => 'visitor@example.com',
        'subject' => 'Test contact',
        'body' => 'This is a test message body.',
    ]);

    Mail::assertQueued(NewMessageNotification::class, function ($mail) {
        $mail->build();

        return $mail->hasTo('admin1@example.com')
            && $mail->hasReplyTo('visitor@example.com');
    });

    Mail::assertQueued(NewMessageNotification::class, function ($mail) {
        $mail->build();

        return $mail->hasTo('admin2@example.com')
            && $mail->hasReplyTo('visitor@example.com');
    });
});

it('sends one bulk email per recipient, skips unsubscribed addresses, and includes unsubscribe headers', function () {
    Mail::fake();

    $sub1 = NewsletterSubscriber::factory()->create(['email' => 'active1@example.com', 'unsubscribed_at' => null]);
    $sub2 = NewsletterSubscriber::factory()->create(['email' => 'active2@example.com', 'unsubscribed_at' => null]);
    $unsub = NewsletterSubscriber::factory()->create(['email' => 'unsub@example.com', 'unsubscribed_at' => now()]);

    $activeSubs = NewsletterSubscriber::whereIn('id', [$sub1->id, $sub2->id, $unsub->id])->active()->get();
    expect($activeSubs->pluck('email')->all())->toContain('active1@example.com', 'active2@example.com')
        ->not->toContain('unsub@example.com');

    foreach ($activeSubs as $sub) {
        $unsubscribeUrl = URL::signedRoute('unsubscribe', ['email' => $sub->email, 'locale' => 'en']);
        $mailable = new AdminComposedMail('Subject', 'Body content', $unsubscribeUrl);
        $mailable->build();

        expect($mailable->headers()->text)->toHaveKey('List-Unsubscribe');
        expect($mailable->headers()->text)->toHaveKey('List-Unsubscribe-Post');
        expect($mailable->textView)->not->toBeEmpty();

        Mail::to($sub->email)->queue($mailable);
    }

    Mail::assertQueued(AdminComposedMail::class, 2);
    Mail::assertQueued(AdminComposedMail::class, fn ($mail) => $mail->hasTo('active1@example.com'));
    Mail::assertQueued(AdminComposedMail::class, fn ($mail) => $mail->hasTo('active2@example.com'));
    Mail::assertNotQueued(AdminComposedMail::class, fn ($mail) => $mail->hasTo('unsub@example.com'));
});

it('filters out registrants who did not tick newsletter consent', function () {
    $event = Event::create([
        'title' => ['en' => 'Consent Event'],
        'event_date' => Carbon::tomorrow()->format('Y-m-d'),
        'registration_open' => true,
    ]);

    $regConsented = EventRegistration::factory()->create([
        'event_id' => $event->id,
        'email' => 'consented@example.com',
        'newsletter_consent' => true,
    ]);

    $regNoConsent = EventRegistration::factory()->create([
        'event_id' => $event->id,
        'email' => 'noconsent@example.com',
        'newsletter_consent' => false,
    ]);

    $consentedList = EventRegistration::where('newsletter_consent', true)->pluck('email');
    expect($consentedList)->toContain('consented@example.com')
        ->not->toContain('noconsent@example.com');
});

it('enforces the daily send limit and remaining capacity tracking', function () {
    $service = app(MailLogService::class);
    SiteSetting::set('mail_daily_limit', '5', 'text', 'mail');

    expect($service->dailyLimit())->toBe(5);

    for ($i = 0; $i < 5; $i++) {
        $service->record("bulk_{$i}@example.com", 'bulk', AdminComposedMail::class);
    }

    expect($service->countToday())->toBeGreaterThanOrEqual(5);
    expect($service->remainingToday())->toBe(0);
    expect($service->bulkWouldExceedLimit(1))->toBeTrue();
});

it('unsubscribes via signed URL and rejects a tampered URL', function () {
    $sub = NewsletterSubscriber::factory()->create([
        'email' => 'user_to_unsub@example.com',
        'unsubscribed_at' => null,
    ]);

    $validUrl = URL::signedRoute('unsubscribe', ['email' => $sub->email, 'locale' => 'en']);
    $response = $this->get($validUrl);
    $response->assertStatus(200);
    $response->assertSee('unsubscribed');

    expect($sub->fresh()->unsubscribed_at)->not->toBeNull();

    // Tampered URL
    $tamperedUrl = $validUrl.'TAMPERED';
    $tamperedResponse = $this->get($tamperedUrl);
    $tamperedResponse->assertStatus(403);
});

it('displays the event details, QR code, and spam folder note on the success page in English', function () {
    $event = Event::create([
        'title' => ['en' => 'Bilingual Showcase Event'],
        'date_label' => ['en' => 'November 20, 2026'],
        'location' => ['en' => 'Toronto, ON'],
        'event_date' => Carbon::tomorrow()->format('Y-m-d'),
        'registration_open' => true,
    ]);

    $response = $this->withSession(['registration_success' => true])->get(route('events.register.thanks', $event));
    $response->assertStatus(200);
    $response->assertSee('November 20, 2026');
    $response->assertSee('Toronto, ON');
    $response->assertSee('<svg', false);
    $response->assertSee('spam');
});

it('displays the spam folder note on the success page in French', function () {
    $event = Event::create([
        'title' => ['en' => 'Bilingual Showcase Event', 'fr' => 'Evenement Bilingue'],
        'date_label' => ['en' => 'November 20, 2026', 'fr' => '20 novembre 2026'],
        'location' => ['en' => 'Toronto, ON', 'fr' => 'Toronto, ON'],
        'event_date' => Carbon::tomorrow()->format('Y-m-d'),
        'registration_open' => true,
    ]);

    app()->setLocale('fr');
    $response = $this->withSession(['locale' => 'fr', 'registration_success' => true])->get(route('events.register.thanks', $event));
    $response->assertStatus(200);
    $response->assertSee('indésirable');
    app()->setLocale('en');
});
