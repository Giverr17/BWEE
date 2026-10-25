<?php

use App\Models\NewsletterSubscriber;
use App\Models\RoadmapItem;
use App\Models\User;

it('renders home page successfully with previews and newsletter', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertSee("BLACK WOMEN'S ENTERPRISE EXCHANGE");
    $response->assertSee('Subscribe to our newsletter');
});

it('renders about page successfully', function () {
    $response = $this->get('/about');

    $response->assertStatus(200);
    $response->assertSee('Research-Informed. Community-Empowered.');
    $response->assertSee('Intersectional EDI');
});

it('renders founders page successfully', function () {
    $response = $this->get('/founders');

    $response->assertStatus(200);
    $response->assertSee('Meet the BWEE Co-Founders');
});

it('renders programs page successfully with roadmap items', function () {
    RoadmapItem::where('slug', 'test-roadmap-feature')->delete();

    $item = RoadmapItem::create([
        'sort_order' => 99,
        'is_active' => true,
        'slug' => 'test-roadmap-feature',
        'title' => 'Test Roadmap Feature Item',
        'description' => 'Test Roadmap Description for testing the future roadmap section.',
        'icon' => '🚀',
        'colour' => '#7C3AED',
    ]);

    $response = $this->get('/programs');

    $response->assertStatus(200);
    $response->assertSee('Comprehensive Programs for Enterprise Scaling');
    $response->assertSee("WHAT'S NEXT", false);
    $response->assertSee('Future Strategic Roadmap');
    $response->assertSee('Test Roadmap Feature Item');
    $response->assertSee('Roadmap 99');

    $item->delete();
});

it('renders events page successfully', function () {
    $response = $this->get('/events');

    $response->assertStatus(200);
    $response->assertSee('Events & Gatherings');
});

it('renders gallery page successfully', function () {
    $response = $this->get('/gallery');

    $response->assertStatus(200);
    $response->assertSee('BWEE Gallery & Recordings', false);
});

it('renders partners page successfully', function () {
    $response = $this->get('/partners');

    $response->assertStatus(200);
    $response->assertSee('Partners & Collaborators', false);
});

it('renders get-involved page successfully', function () {
    $response = $this->get('/get-involved');

    $response->assertStatus(200);
    $response->assertSee('Get Involved with BWEE');
});

it('renders contact page successfully', function () {
    $response = $this->get('/contact');

    $response->assertStatus(200);
    $response->assertSee('Get in Touch');
    $response->assertSee('Send Message');
});

it('subscribes to newsletter successfully', function () {
    $email = 'test_'.uniqid().'@example.com';
    $response = $this->post('/newsletter', [
        'email' => $email,
    ]);

    $response->assertSessionHas('newsletter_success');
    expect(NewsletterSubscriber::where('email', $email)->exists())->toBeTrue();
});

it('handles duplicate newsletter subscriptions gracefully', function () {
    $email = 'duplicate_'.uniqid().'@example.com';
    NewsletterSubscriber::create([
        'email' => $email,
        'subscribed_at' => now(),
    ]);

    $response = $this->post('/newsletter', [
        'email' => $email,
    ]);

    $response->assertSessionHas('newsletter_success');
    expect(NewsletterSubscriber::where('email', $email)->count())->toBe(1);
});

it('renders admin partners page for authenticated users', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('admin.partners'));

    $response->assertStatus(200);
    $response->assertSee('Partners');
});

it('renders admin gallery page for authenticated users', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('admin.gallery'));

    $response->assertStatus(200);
    $response->assertSee('Gallery');
});

it('renders admin settings page for authenticated users', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('admin.settings'));

    $response->assertStatus(200);
    $response->assertSee('Site Settings');
});
