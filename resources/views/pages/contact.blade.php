@extends('layouts.app')

@section('title', __('contact.hero.title', ['site' => $settings['site_name'] ?? 'BWEE']))
@section('meta_description', __('contact.hero.meta'))

@section('content')
<section class="subpage-hero">
  <div class="wrap">
    <div class="hero-eyebrow">{{ __('contact.hero.eyebrow') }}</div>
    <h1>{{ __('contact.hero.heading') }}</h1>
    <p>
      {{ __('contact.hero.text') }}
    </p>
  </div>
</section>

<section id="contact" style="padding: 80px 0;">
  <div class="wrap">
    @if(session('contact_success'))
      <div class="contact-success" role="alert">
        ✅ {{ session('contact_success') }}
      </div>
    @endif

    <div class="contact-grid" style="margin-top: 0;">
      {{-- Left: Contact info --}}
      <div class="contact-info">
        <div class="contact-info-item">
          <span class="ci-icon">✉️</span>
          <div>
            <strong>{{ __('contact.info.email_label') }}</strong>
            <a href="mailto:{{ $settings['contact_email'] ?? 'blackwomenenterpriseexchange@gmail.com' }}">{{ $settings['contact_email'] ?? 'blackwomenenterpriseexchange@gmail.com' }}</a>
          </div>
        </div>

        <div class="contact-info-item">
          <span class="ci-icon">🏛️</span>
          <div>
            <strong>{{ __('contact.info.inst_label') }}</strong>
            <span>{{ __('contact.info.inst_value') }}</span>
          </div>
        </div>

        <div class="contact-info-item">
          <span class="ci-icon">🌍</span>
          <div>
            <strong>{{ __('contact.info.reach_label') }}</strong>
            <span>{{ __('contact.info.reach_value') }}</span>
          </div>
        </div>
      </div>

      {{-- Right: Contact form --}}
      <form action="{{ route('contact.store') }}" method="POST" class="contact-form" id="contactForm">
        @csrf
        <div class="form-row">
          <div class="form-group">
            <label for="contact_name">{{ __('contact.form.name_label') }} <span class="req">*</span></label>
            <input type="text" id="contact_name" name="name" value="{{ old('name') }}" required placeholder="{{ __('contact.form.name_placeholder') }}">
            @error('name')<span class="field-error">{{ $message }}</span>@enderror
          </div>
          <div class="form-group">
            <label for="contact_email">{{ __('contact.form.email_label') }} <span class="req">*</span></label>
            <input type="email" id="contact_email" name="email" value="{{ old('email') }}" required placeholder="{{ __('contact.form.email_placeholder') }}">
            @error('email')<span class="field-error">{{ $message }}</span>@enderror
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="contact_interest">{{ __('contact.form.interest_label') }}</label>
            <select id="contact_interest" name="interest">
              <option value="">{{ __('contact.form.interest_default') }}</option>
              <option value="membership" {{ old('interest') === 'membership' ? 'selected' : '' }}>{{ __('contact.form.interest_member') }}</option>
              <option value="partner"    {{ old('interest') === 'partner'    ? 'selected' : '' }}>{{ __('contact.form.interest_partner') }}</option>
              <option value="volunteer"  {{ old('interest') === 'volunteer'  ? 'selected' : '' }}>{{ __('contact.form.interest_volunteer') }}</option>
              <option value="other"      {{ old('interest') === 'other'      ? 'selected' : '' }}>{{ __('contact.form.interest_other') }}</option>
            </select>
          </div>
          <div class="form-group">
            <label for="contact_subject">{{ __('contact.form.subject_label') }}</label>
            <input type="text" id="contact_subject" name="subject" value="{{ old('subject') }}" placeholder="{{ __('contact.form.subject_placeholder') }}">
            @error('subject')<span class="field-error">{{ $message }}</span>@enderror
          </div>
        </div>

        <div class="form-group">
          <label for="contact_body">{{ __('contact.form.message_label') }} <span class="req">*</span></label>
          <textarea id="contact_body" name="body" rows="6" required placeholder="{{ __('contact.form.message_placeholder') }}">{{ old('body') }}</textarea>
          @error('body')<span class="field-error">{{ $message }}</span>@enderror
        </div>

        <button type="submit" class="btn btn-primary" id="contactSubmit" style="align-self: flex-start; cursor: pointer; border: none; font-size: 0.95rem;">{{ __('contact.form.submit') }}</button>
      </form>
    </div>
  </div>
</section>
@endsection