@extends('layouts.app')

@section('title', __('registration.page_title', ['event' => $event->title]))
@section('meta_description', __('registration.meta_description', ['event' => $event->title]))

@section('content')
<section class="subpage-hero">
  <div class="wrap">
    <div class="hero-eyebrow">{{ __('registration.form_heading') }}</div>
    <h1>{{ $event->title }}</h1>
    <p>
      {{ __('registration.form_subtitle') }}
    </p>
  </div>
</section>

<section id="registration" style="padding: 70px 0;">
  <div class="wrap">
    <div class="contact-grid" style="margin-top: 0;">
      {{-- Left: Event Summary Details --}}
      <div class="contact-info">
        <div style="background: rgba(255, 255, 255, 0.04); border: 1px solid var(--line); border-radius: 14px; padding: 24px;">
          <h3 style="font-family: 'Fraunces', serif; font-size: 1.25rem; color: var(--on-dark); margin-bottom: 16px;">
            {{ __('registration.event_details') }}
          </h3>

          <div style="display: flex; flex-direction: column; gap: 18px;">
            @if($event->date_label)
              <div class="contact-info-item">
                <span class="ci-icon">📅</span>
                <div>
                  <strong>{{ __('registration.date') }}</strong>
                  <span>{{ $event->date_label }}</span>
                </div>
              </div>
            @endif

            @if($event->location)
              <div class="contact-info-item">
                <span class="ci-icon">📍</span>
                <div>
                  <strong>{{ __('registration.location') }}</strong>
                  <span>{{ $event->location }}</span>
                </div>
              </div>
            @endif

            @if($event->description)
              <div style="margin-top: 10px; font-size: 0.9rem; color: var(--ink-soft); line-height: 1.6; border-top: 1px solid var(--line); padding-top: 16px;">
                {{ \Illuminate\Support\Str::limit($event->description, 280) }}
              </div>
            @endif
          </div>
        </div>
      </div>

      {{-- Right: Registration Form --}}
      <div>
        <form action="{{ route('events.register.store', $event) }}" method="POST" class="contact-form" id="eventRegistrationForm">
          @csrf

          {{-- Honeypot field (hidden from genuine users) --}}
          <div style="display: none !important;" aria-hidden="true">
            <label for="reg_website">Website</label>
            <input type="text" name="website" id="reg_website" tabindex="-1" autocomplete="off">
          </div>

          {{-- 1. Full Name & 2. Email --}}
          <div class="form-row">
            <div class="form-group">
              <label for="reg_full_name">{{ __('registration.full_name_label') }} <span class="req">*</span></label>
              <input type="text"
                     id="reg_full_name"
                     name="full_name"
                     value="{{ old('full_name') }}"
                     required
                     placeholder="{{ __('registration.full_name_placeholder') }}"
                     aria-describedby="err_full_name">
              @error('full_name')
                <span class="field-error" id="err_full_name">{{ $message }}</span>
              @enderror
            </div>

            <div class="form-group">
              <label for="reg_email">{{ __('registration.email_label') }} <span class="req">*</span></label>
              <input type="email"
                     id="reg_email"
                     name="email"
                     value="{{ old('email') }}"
                     required
                     placeholder="{{ __('registration.email_placeholder') }}"
                     aria-describedby="err_email">
              @error('email')
                <span class="field-error" id="err_email">{{ $message }}</span>
              @enderror
            </div>
          </div>

          {{-- 3. Mobile Number --}}
          <div class="form-group">
            <label for="reg_phone">{{ __('registration.phone_label') }} <span class="req">*</span></label>
            <input type="tel"
                   id="reg_phone"
                   name="phone"
                   value="{{ old('phone') }}"
                   required
                   placeholder="{{ __('registration.phone_placeholder') }}"
                   aria-describedby="err_phone">
            @error('phone')
              <span class="field-error" id="err_phone">{{ $message }}</span>
            @enderror
          </div>

          {{-- 4. Country of Residence & 5. Nationality --}}
          <div class="form-row">
            <div class="form-group">
              <label for="reg_country_of_residence">{{ __('registration.country_residence_label') }} <span class="req">*</span></label>
              <select id="reg_country_of_residence"
                      name="country_of_residence"
                      required
                      aria-describedby="err_country_of_residence">
                <option value="">{{ __('registration.country_residence_placeholder') }}</option>
                @foreach($countries as $code => $countryName)
                  <option value="{{ $code }}" {{ old('country_of_residence') === $code ? 'selected' : '' }}>
                    {{ $countryName }}
                  </option>
                @endforeach
              </select>
              @error('country_of_residence')
                <span class="field-error" id="err_country_of_residence">{{ $message }}</span>
              @enderror
            </div>

            <div class="form-group">
              <label for="reg_nationality">{{ __('registration.nationality_label') }} <span class="req">*</span></label>
              <select id="reg_nationality"
                      name="nationality"
                      required
                      aria-describedby="err_nationality">
                <option value="">{{ __('registration.nationality_placeholder') }}</option>
                @foreach($countries as $code => $countryName)
                  <option value="{{ $code }}" {{ old('nationality') === $code ? 'selected' : '' }}>
                    {{ $countryName }}
                  </option>
                @endforeach
              </select>
              @error('nationality')
                <span class="field-error" id="err_nationality">{{ $message }}</span>
              @enderror
            </div>
          </div>

          {{-- 6. Profession & 7. Organization --}}
          <div class="form-row">
            <div class="form-group">
              <label for="reg_profession">{{ __('registration.profession_label') }} <span class="req">*</span></label>
              <input type="text"
                     id="reg_profession"
                     name="profession"
                     value="{{ old('profession') }}"
                     required
                     placeholder="{{ __('registration.profession_placeholder') }}"
                     aria-describedby="err_profession">
              @error('profession')
                <span class="field-error" id="err_profession">{{ $message }}</span>
              @enderror
            </div>

            <div class="form-group">
              <label for="reg_organization">{{ __('registration.organization_label') }}</label>
              <input type="text"
                     id="reg_organization"
                     name="organization"
                     value="{{ old('organization') }}"
                     placeholder="{{ __('registration.organization_placeholder') }}"
                     aria-describedby="err_organization">
              @error('organization')
                <span class="field-error" id="err_organization">{{ $message }}</span>
              @enderror
            </div>
          </div>

          {{-- 8. Expectations (textarea, max 1000) --}}
          <div class="form-group">
            <label for="reg_expectations">{{ __('registration.expectations_label') }} <span class="req">*</span></label>
            <textarea id="reg_expectations"
                      name="expectations"
                      rows="4"
                      maxlength="1000"
                      required
                      placeholder="{{ __('registration.expectations_placeholder') }}"
                      aria-describedby="err_expectations">{{ old('expectations') }}</textarea>
            @error('expectations')
              <span class="field-error" id="err_expectations">{{ $message }}</span>
            @enderror
          </div>

          {{-- 9. Consent Checkbox --}}
          <div class="form-group" style="margin-top: 6px;">
            <label style="display: flex; align-items: flex-start; gap: 12px; cursor: pointer; text-transform: none; font-size: 0.9rem; font-weight: 500; color: var(--ink);">
              <input type="checkbox"
                     name="consent"
                     id="reg_consent"
                     value="1"
                     {{ old('consent') ? 'checked' : '' }}
                     required
                     style="width: 18px; height: 18px; margin-top: 2px; accent-color: var(--marigold); flex-shrink: 0;"
                     aria-describedby="err_consent">
              <span>
                <strong>{{ __('registration.consent_label') }}</strong><br>
                <span style="font-size: 0.82rem; color: var(--ink-soft); line-height: 1.45; display: inline-block; margin-top: 4px;">
                  {{ __('registration.consent_text') }}
                </span>
              </span>
            </label>
            @error('consent')
              <span class="field-error" id="err_consent">{{ $message }}</span>
            @enderror
          </div>

          {{-- Newsletter opt-in (optional, unchecked by default) --}}
          <div class="form-group" style="margin-top: 6px;">
            <label style="display: flex; align-items: flex-start; gap: 12px; cursor: pointer; text-transform: none; font-size: 0.9rem; font-weight: 500; color: var(--ink);">
              <input type="checkbox"
                     name="newsletter_opt_in"
                     id="reg_newsletter_opt_in"
                     value="1"
                     {{ old('newsletter_opt_in', old('newsletter_consent')) ? 'checked' : '' }}
                     style="width: 18px; height: 18px; margin-top: 2px; accent-color: var(--marigold); flex-shrink: 0;">
              <span>
                <strong>{{ __('registration.newsletter_consent_label') }}</strong><br>
                <span style="font-size: 0.82rem; color: var(--ink-soft); line-height: 1.45; display: inline-block; margin-top: 4px;">
                  {{ __('registration.newsletter_consent_text') }}
                </span>
              </span>
            </label>
          </div>

          {{-- Spam folder note --}}
          <p style="font-size: 0.82rem; color: var(--ink-soft); line-height: 1.45; margin-top: 14px;">
            {{ __('registration.spam_folder_note') }}
          </p>

          {{-- Submit button --}}
          <div style="margin-top: 10px;">
            <button type="submit"
                    class="btn btn-primary"
                    id="submitRegistration"
                    style="cursor: pointer; border: none; font-size: 0.95rem; padding: 12px 28px;">
              {{ __('registration.submit_button') }} &rarr;
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>
@endsection
