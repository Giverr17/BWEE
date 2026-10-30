<?php

use App\Mail\NewMessageNotification;
use App\Mail\RegistrationConfirmation;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\NewsletterSubscriber;
use App\Models\SiteSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Livewire\Volt\Volt;

uses(DatabaseTransactions::class);

it('renders confirmation email in French for French registrant', function () {
    $event = Event::create([
        'title' => ['en' => 'Bilingual Summit', 'fr' => 'Sommet Bilingue'],
        'date_label' => ['en' => 'Dec 1, 2026', 'fr' => '1 dec 2026'],
        'location' => ['en' => 'Montreal', 'fr' => 'Montréal'],
        'event_date' => Carbon::tomorrow()->format('Y-m-d'),
        'registration_open' => true,
    ]);

    $reg = EventRegistration::factory()->create([
        'event_id' => $event->id,
        'full_name' => 'Amélie Laurent',
        'email' => 'amelie@test.com',
        'locale' => 'fr',
    ]);

    $mailable = new RegistrationConfirmation($reg);
    $mailable->build();

    expect($mailable->locale)->toBe('fr');
    expect($mailable->subject)->toContain("Confirmation d'inscription");

    $html = $mailable->render();
    expect($html)->toContain('Votre inscription a bien été enregistrée');
    expect($html)->toContain('Sommet Bilingue');
    expect($html)->toContain('Montréal');
});

it('renders confirmation email in English for English registrant', function () {
    $event = Event::create([
        'title' => ['en' => 'English Summit', 'fr' => 'Sommet Anglais'],
        'date_label' => ['en' => 'Dec 1, 2026', 'fr' => '1 dec 2026'],
        'location' => ['en' => 'Toronto', 'fr' => 'Toronto'],
        'event_date' => Carbon::tomorrow()->format('Y-m-d'),
        'registration_open' => true,
    ]);

    $reg = EventRegistration::factory()->create([
        'event_id' => $event->id,
        'full_name' => 'Sarah Smith',
        'email' => 'sarah@test.com',
        'locale' => 'en',
    ]);

    $mailable = new RegistrationConfirmation($reg);
    $mailable->build();

    expect($mailable->locale)->toBe('en');
    expect($mailable->subject)->toContain('Registration confirmed');

    $html = $mailable->render();
    expect($html)->toContain('Your registration has been confirmed');
    expect($html)->toContain('English Summit');
});

it('falls back to English event fields when French translation is empty in confirmation email', function () {
    $event = Event::create([
        'title' => ['en' => 'Only English Title'],
        'date_label' => ['en' => 'October 15, 2026'],
        'location' => ['en' => 'Halifax, NS'],
        'event_date' => Carbon::tomorrow()->format('Y-m-d'),
        'registration_open' => true,
    ]);

    $reg = EventRegistration::factory()->create([
        'event_id' => $event->id,
        'full_name' => 'Jeanne Martin',
        'email' => 'jeanne@test.com',
        'locale' => 'fr',
    ]);

    $mailable = new RegistrationConfirmation($reg);
    $mailable->build();

    $html = $mailable->render();
    expect($html)->toContain('Only English Title');
    expect($html)->toContain('October 15, 2026');
    expect($html)->toContain('Halifax, NS');
});

it('preserves the locale on queued registration confirmation mail', function () {
    Mail::fake();

    $event = Event::create([
        'title' => ['en' => 'Queued Mail Event', 'fr' => 'Evenement File'],
        'event_date' => Carbon::tomorrow()->format('Y-m-d'),
        'registration_open' => true,
    ]);

    $this->withSession(['locale' => 'fr'])->post('/register/'.$event->slug, [
        'full_name' => 'Fatou Diop',
        'email' => 'fatou@test.com',
        'phone' => '+1 514 555 0188',
        'country_of_residence' => 'CA',
        'nationality' => 'SN',
        'profession' => 'Entrepreneur',
        'expectations' => 'Learning about the ecosystem.',
        'consent' => '1',
        'newsletter_opt_in' => '1',
    ]);

    Mail::assertQueued(RegistrationConfirmation::class, function ($mail) {
        return $mail->hasTo('fatou@test.com') && $mail->locale === 'fr';
    });
});

it('includes the visitor language line in contact notification alerts while email stays in English', function () {
    Mail::fake();
    SiteSetting::set('contact_notification_emails', 'founder@bwee.org');

    $this->withSession(['locale' => 'fr'])->post('/contact', [
        'name' => 'Claire Dupont',
        'email' => 'claire@test.com',
        'subject' => 'Question bilingue',
        'body' => 'Bonjour, ceci est un message.',
    ]);

    Mail::assertQueued(NewMessageNotification::class, function ($mail) {
        $mail->build();
        $html = $mail->render();

        return $mail->hasTo('founder@bwee.org')
            && str_contains($html, 'Visitor site language: French (Français)')
            && str_contains($html, 'New message from the BWEE website');
    });
});

it('has the newsletter consent checkbox unchecked by default on the registration form', function () {
    $event = Event::create([
        'title' => ['en' => 'Checkbox Test Event'],
        'event_date' => Carbon::tomorrow()->format('Y-m-d'),
        'registration_open' => true,
    ]);

    $response = $this->get('/register/'.$event->slug);
    $response->assertStatus(200);
    $response->assertSee('name="newsletter_opt_in"', false);
    $response->assertDontSee('name="newsletter_opt_in" value="1" checked', false);
});

it('creates a newsletter subscriber when registration newsletter checkbox is ticked', function () {
    Mail::fake();

    $event = Event::create([
        'title' => ['en' => 'Opt-In Test Event'],
        'event_date' => Carbon::tomorrow()->format('Y-m-d'),
        'registration_open' => true,
    ]);

    $this->post('/register/'.$event->slug, [
        'full_name' => 'Nia Johnson',
        'email' => 'nia@test.com',
        'phone' => '+1 416 555 0144',
        'country_of_residence' => 'CA',
        'nationality' => 'JM',
        'profession' => 'Consultant',
        'expectations' => 'Networking and growth.',
        'consent' => '1',
        'newsletter_opt_in' => '1',
    ]);

    $reg = EventRegistration::where('email', 'nia@test.com')->first();
    expect($reg)->not->toBeNull();
    expect($reg->newsletter_opt_in)->toBeTrue();
    expect($reg->consented_at)->not->toBeNull();

    $sub = NewsletterSubscriber::where('email', 'nia@test.com')->first();
    expect($sub)->not->toBeNull();
    expect($sub->source)->toBe('registration');
    expect($sub->consented_at)->not->toBeNull();
});

it('does not duplicate subscriber if registrant is already subscribed', function () {
    Mail::fake();

    NewsletterSubscriber::create([
        'email' => 'existing@test.com',
        'name' => 'Existing Person',
        'locale' => 'en',
        'source' => 'web',
        'subscribed_at' => now(),
    ]);

    $event = Event::create([
        'title' => ['en' => 'Duplicate Test Event'],
        'event_date' => Carbon::tomorrow()->format('Y-m-d'),
        'registration_open' => true,
    ]);

    $this->post('/register/'.$event->slug, [
        'full_name' => 'Existing Person Updated',
        'email' => 'existing@test.com',
        'phone' => '+1 416 555 0145',
        'country_of_residence' => 'CA',
        'nationality' => 'CA',
        'profession' => 'Founder',
        'expectations' => 'Scale enterprise.',
        'consent' => '1',
        'newsletter_opt_in' => '1',
    ]);

    expect(NewsletterSubscriber::where('email', 'existing@test.com')->count())->toBe(1);
});

it('does not create a newsletter subscriber when checkbox is not ticked', function () {
    Mail::fake();

    $event = Event::create([
        'title' => ['en' => 'No Opt-In Event'],
        'event_date' => Carbon::tomorrow()->format('Y-m-d'),
        'registration_open' => true,
    ]);

    $this->post('/register/'.$event->slug, [
        'full_name' => 'Private User',
        'email' => 'private@test.com',
        'phone' => '+1 416 555 0146',
        'country_of_residence' => 'CA',
        'nationality' => 'CA',
        'profession' => 'Specialist',
        'expectations' => 'Attend event only.',
        'consent' => '1',
    ]);

    $reg = EventRegistration::where('email', 'private@test.com')->first();
    expect($reg)->not->toBeNull();
    expect($reg->newsletter_opt_in)->toBeFalse();
    expect($reg->consented_at)->toBeNull();

    expect(NewsletterSubscriber::where('email', 'private@test.com')->exists())->toBeFalse();
});

it('subscribes via newsletter Livewire component without reload and with inline feedback', function () {
    Volt::test('newsletter-form')
        ->set('email', 'livewire_sub@test.com')
        ->call('subscribe')
        ->assertSet('status', 'success')
        ->assertSet('email', '');

    expect(NewsletterSubscriber::where('email', 'livewire_sub@test.com')->exists())->toBeTrue();
});

it('shows friendly message when already subscribed in newsletter Livewire component', function () {
    NewsletterSubscriber::create([
        'email' => 'already_livewire@test.com',
        'locale' => 'en',
        'source' => 'web',
        'subscribed_at' => now(),
    ]);

    Volt::test('newsletter-form')
        ->set('email', 'already_livewire@test.com')
        ->call('subscribe')
        ->assertSet('status', 'already');

    expect(NewsletterSubscriber::where('email', 'already_livewire@test.com')->count())->toBe(1);
});

it('dispatches toast event when saving settings in admin', function () {
    $user = User::factory()->create();

    Volt::actingAs($user)
        ->test('admin.manage-settings')
        ->call('save')
        ->assertDispatched('toast', function ($event, $params) {
            return ($params['type'] ?? '') === 'success';
        });
});

it('filters registrations by newsletter opt-in status in admin registrations component', function () {
    $user = User::factory()->create();

    $event = Event::create([
        'title' => ['en' => 'Admin Filter Event'],
        'event_date' => Carbon::tomorrow()->format('Y-m-d'),
        'registration_open' => true,
    ]);

    EventRegistration::factory()->create([
        'event_id' => $event->id,
        'email' => 'optedin@test.com',
        'newsletter_opt_in' => true,
    ]);

    EventRegistration::factory()->create([
        'event_id' => $event->id,
        'email' => 'notopted@test.com',
        'newsletter_opt_in' => false,
    ]);

    Volt::actingAs($user)
        ->test('admin.manage-registrations')
        ->set('selectedEventId', $event->id)
        ->set('newsletterFilter', 'yes')
        ->assertSee('optedin@test.com')
        ->assertDontSee('notopted@test.com');
});
