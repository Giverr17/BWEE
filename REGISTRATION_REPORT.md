# Event Registration Report

## Summary

Full event registration system added to BWEE. Visitors register for events through public bilingual pages
with unique URLs and QR codes. Admins manage registrations, export CSV, and control registration status per event.

---

## Packages Added

- `symfony/intl ^8.1` - Country names in EN and FR
- `symfony/polyfill-intl-icu ^1.43` - ICU polyfill (intl PHP extension also enabled in php.ini)
- `bacon/bacon-qr-code ^3.1` - SVG QR code generation (no GD or Imagick required)

---

## Migrations Added (new files only)

| File | Description |
|---|---|
| `2026_10_27_112821_add_slug_and_registration_open_to_events_table.php` | Adds nullable `slug` (unique) and `registration_open` (bool, default false) to events. Backfills slugs from English title with numeric suffix on collision. |
| `2026_10_27_113123_create_event_registrations_table.php` | Creates `event_registrations` table with all required fields, unique index on (event_id, email), index on event_id. |

Both migrations are SQLite and MySQL compatible. Existing event rows survive.

---

## Files Changed

| File | What Changed |
|---|---|
| `app/Models/Event.php` | Added slug/registration_open to fillable/casts; `booted()` auto-slug on create; `generateUniqueSlug()`; `getRouteKeyName()`; `registrations()` hasMany; `isRegistrationOpen()`; modal_data includes register_url and is_registration_open. |
| `app/Models/EventRegistration.php` | New model: fillable, casts, belongsTo Event, localized country name accessors (EN and current-locale). |
| `app/Services/QrCodeService.php` | New service: `generateSvg()` using BaconQrCode SVG backend, size 512, margin 2, EC level M. |
| `app/Http/Controllers/EventRegistrationController.php` | New controller: `create()`, `store()` (honeypot, duplicate check, throttle), `thanks()`. |
| `database/seeders/EventSeeder.php` | Added `slug` and `registration_open = false` to launch event only. No other text changed. |
| `resources/views/livewire/admin/manage-events.blade.php` | Slug field (auto-filled from EN title), registration open switch, QR Code modal, registration count and Open/Closed chip in table. |
| `resources/views/livewire/admin/manage-registrations.blade.php` | New Volt component: event dropdown, search, expandable rows for expectations text, delete with confirm, Export CSV button. |
| `resources/views/admin/registrations.blade.php` | New admin page shell hosting the Volt component. |
| `resources/views/components/layouts/admin.blade.php` | Added `totalRegistrations`; Registrations sidebar link with badge. |
| `resources/views/partials/event-card.blade.php` | Shows Register button when `isRegistrationOpen()` and slug is set. |
| `resources/views/partials/event-modal.blade.php` | Shows Register button in modal footer when registration is open. |
| `resources/views/pages/events/register.blade.php` | New: public bilingual registration form (all 9 fields, honeypot, accessible labels with aria-describedby error linking). |
| `resources/views/pages/events/register-closed.blade.php` | New: friendly bilingual closed notice, HTTP 200. |
| `resources/views/pages/events/register-thanks.blade.php` | New: success and already-registered confirmation pages. |
| `lang/en/events.php` | Added `register` key. |
| `lang/fr/events.php` | Added `register` key. |
| `lang/en/registration.php` | New: all registration UI strings in English. |
| `lang/fr/registration.php` | New: all registration UI strings in Canadian French. |
| `routes/web.php` | Public registration routes, QR SVG download, CSV export route, admin registrations route. |
| `tests/Feature/EventRegistrationTest.php` | New: 15 tests covering all required scenarios. |
| `composer.json` / `composer.lock` | Updated with three new packages. |

---

## Routes Added

| Method | URI | Name | Notes |
|---|---|---|---|
| GET | `/register/{event:slug}` | `events.register` | Public form or closed page |
| POST | `/register/{event:slug}` | `events.register.store` | Throttled 6/min per IP |
| GET | `/register/{event:slug}/thanks` | `events.register.thanks` | Success or already-registered |
| GET | `/switch/events/{event}/qr-svg` | `admin.events.qr-svg` | Auth required; returns SVG attachment |
| GET | `/switch/registrations` | `admin.registrations` | Auth required; manage-registrations component |
| GET | `/switch/registrations/export-csv/{event}` | `admin.registrations.export-csv` | Auth required; streams UTF-8 CSV with BOM |

---

## Tests (tests/Feature/EventRegistrationTest.php)

| Test | Result |
|---|---|
| auto-generates slug and uniqueness correctly | PASS |
| isRegistrationOpen for future/today/past/switch-off | PASS |
| renders form in en and fr | PASS |
| shows closed page with HTTP 200 | PASS |
| returns 404 for unknown slug | PASS |
| stores registration with event_id, consent_at, locale | PASS |
| duplicate email same event: no second row, friendly notice | PASS |
| same email different events: both records stored | PASS |
| validation errors for bad email/phone/missing consent/invalid country | PASS |
| honeypot: no row stored, pretends success | PASS |
| throttle: 429 after 6 requests per minute | PASS |
| admin registrations requires auth, lists and filters rows | PASS |
| CSV export: correct headers, BOM, English country names | PASS |
| QR SVG download: correct content-type and filename | PASS |
| backfill: every existing event has a unique slug | PASS |

**Full test suite: 79/79 passed, 356 assertions.**

---

## French Strings Table (lang/fr/registration.php)

All strings authored for human review. Accented characters appear correctly in the actual file.

| Key | French String |
|---|---|
| `page_title` | Inscription : :event | BWEE |
| `meta_description` | Inscrivez-vous a l'evenement :event organise par l'Echange d'entreprises pour femmes noires (BWEE). |
| `closed_title` | Les inscriptions sont closes |
| `closed_heading` | Les inscriptions sont closes |
| `closed_message` | Les inscriptions pour l'evenement :event sont actuellement closes. |
| `closed_bilingual_notice` | Merci pour votre interet envers les activites de BWEE. Nous vous invitons a consulter nos prochains programmes. |
| `back_to_events` | Retour aux evenements |
| `event_details` | Details de l'evenement |
| `date` | Date |
| `location` | Lieu |
| `form_heading` | Inscription a l'evenement |
| `form_subtitle` | Veuillez remplir le formulaire ci-dessous pour reserver votre place. |
| `full_name_label` | Nom complet |
| `full_name_placeholder` | ex. Jane Doe |
| `email_label` | Adresse courriel |
| `email_placeholder` | ex. jane@exemple.com |
| `phone_label` | Numero de cellulaire |
| `phone_placeholder` | ex. +1 416 555 0199 |
| `country_residence_label` | Pays de residence |
| `country_residence_placeholder` | Selectionnez votre pays de residence |
| `nationality_label` | Nationalite |
| `nationality_placeholder` | Selectionnez votre nationalite |
| `profession_label` | Profession ou domaine d'interet |
| `profession_placeholder` | ex. Technologies, commerce de detail, sante |
| `organization_label` | Organisation ou entreprise |
| `organization_placeholder` | Facultatif |
| `expectations_label` | Qu'esperez-vous retirer de cette seance ? |
| `expectations_placeholder` | Faites-nous part de vos attentes, objectifs ou questions... |
| `consent_label` | Je consens a la collecte et a l'utilisation de ces renseignements. |
| `consent_text` | Les renseignements recueillis sont utilises uniquement pour gerer votre inscription et communiquer avec vous au sujet de cet evenement. |
| `submit_button` | Finaliser l'inscription |
| `submitting` | Inscription en cours... |
| `success_title` | Inscription confirmee |
| `success_heading` | Merci pour votre inscription |
| `success_message` | Votre inscription a l'evenement :event a bien ete enregistree. Nous avons hate de vous compter parmi nous. |
| `already_registered_title` | Deja inscrit(e) |
| `already_registered_message` | Vous etes deja inscrit(e) a l'evenement :event avec cette adresse courriel. |
| `register_button` | S'inscrire |

Note: Inclusive wording "inscrit(e)" is used throughout to cover all participant genders.

---

## Items Skipped / Notes

| Item | Note |
|---|---|
| DeepL auto-translation for slug | Not applicable; slug is a URL-safe identifier, not bilingual text. |
| exportCsv Livewire method | Moved to dedicated route `admin.registrations.export-csv` because Livewire 4 streaming response API is incompatible with testing via `->call()`. The UI button links to this route. |
| Email notification on registration | Not in scope per the task specification. |
| Noindex on registration pages | Task explicitly said "No: allow indexing." Pages have proper title and meta description per event. |
| `php.ini` intl extension | Enabled (was commented out in XAMPP). The Nix production environment (`php84` package) includes `intl` natively. |