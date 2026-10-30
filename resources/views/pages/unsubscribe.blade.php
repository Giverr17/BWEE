@extends('layouts.app')

@section('title', app()->getLocale() === 'fr' ? 'Desabonnement | BWEE' : 'Unsubscribe | BWEE')

@section('content')
<section style="padding: 90px 0; min-height: 60vh;">
    <div class="wrap" style="max-width: 600px; text-align: center; margin: 0 auto;">
        <div style="background: rgba(255, 255, 255, 0.04); border: 1px solid var(--line); border-radius: 18px; padding: 48px 32px;">

            <div style="font-size: 3rem; margin-bottom: 20px;">&#10003;</div>

            @if(app()->getLocale() === 'fr')
                <h1 style="font-family: 'Fraunces', serif; font-size: 1.8rem; color: var(--marigold); margin-bottom: 16px;">Desabonnement effectue</h1>
                <p style="font-size: 1.05rem; color: var(--ink); line-height: 1.6; margin-bottom: 24px;">
                    L'adresse <strong>{{ $email }}</strong> a ete retiree de notre liste d'envoi.
                    Vous ne recevrez plus nos messages collectifs.
                </p>
                <p style="font-size: 0.9rem; color: var(--ink-soft); line-height: 1.6;">
                    Vous continuez a recevoir les emails transactionnels lies a vos inscriptions aux evenements.
                </p>
            @else
                <h1 style="font-family: 'Fraunces', serif; font-size: 1.8rem; color: var(--marigold); margin-bottom: 16px;">You have been unsubscribed</h1>
                <p style="font-size: 1.05rem; color: var(--ink); line-height: 1.6; margin-bottom: 24px;">
                    The address <strong>{{ $email }}</strong> has been removed from our mailing list.
                    You will no longer receive bulk messages from us.
                </p>
                <p style="font-size: 0.9rem; color: var(--ink-soft); line-height: 1.6;">
                    You will still receive transactional emails related to your event registrations.
                </p>
            @endif

            <div style="margin-top: 32px;">
                <a href="{{ route('home') }}" class="btn btn-primary" style="text-decoration: none; padding: 12px 28px;">
                    @if(app()->getLocale() === 'fr')
                        Retour a l'accueil
                    @else
                        Back to home
                    @endif
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
