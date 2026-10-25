<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<meta name="csrf-token" content="{{ csrf_token() }}" />
<meta name="description" content="{{ __('top_donors.page_subtitle') }}" />
<meta name="theme-color" content="#F5F4F1" />
<title>{{ __('top_donors.page_title') }} — {{ config('app.name', 'NUBL') }}</title>
<link rel="canonical" href="{{ route('top-donors.index') }}" />
@include('partials.favicons')

@vite(['resources/css/welcome-landing.css'])

<style>
/* Top-Donors page-specific overrides */
.td-hero {
  background: var(--color-base, #F5F4F1);
  padding: clamp(5rem, 10vw, 8rem) 1.5rem clamp(3rem, 6vw, 5rem);
  text-align: center;
  border-bottom: 1px solid rgba(0,0,0,.07);
}
.td-hero .eyebrow {
  font-size: .8rem;
  font-weight: 600;
  letter-spacing: .1em;
  text-transform: uppercase;
  color: var(--accent, #F0AA1F);
  margin-bottom: .75rem;
}
.td-hero h1 {
  font-size: clamp(1.8rem, 4vw, 3rem);
  font-weight: 800;
  color: var(--color-ink, #1a1a1a);
  line-height: 1.15;
  margin: 0 auto .75rem;
  max-width: 640px;
}
.td-hero p {
  font-size: clamp(.95rem, 2vw, 1.1rem);
  color: var(--color-ink-muted, #555);
  max-width: 520px;
  margin: 0 auto;
  line-height: 1.7;
}

.td-section {
  max-width: 820px;
  margin: 0 auto;
  padding: clamp(2.5rem, 5vw, 4rem) 1.5rem clamp(4rem, 8vw, 7rem);
}

/* Table */
.td-table-wrap {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  border-radius: 12px;
  border: 1px solid rgba(0,0,0,.08);
  box-shadow: 0 2px 16px rgba(0,0,0,.05);
}
.td-table {
  width: 100%;
  border-collapse: collapse;
  font-size: .95rem;
  background: #fff;
}
.td-table thead tr {
  background: var(--color-base, #F5F4F1);
  border-bottom: 2px solid rgba(0,0,0,.08);
}
.td-table th {
  padding: .85rem 1.25rem;
  font-size: .75rem;
  font-weight: 700;
  letter-spacing: .06em;
  text-transform: uppercase;
  color: var(--color-ink-muted, #777);
  text-align: start;
}
.td-table th.td-th-rank {
  width: 60px;
  text-align: center;
}
.td-table th.td-th-amount {
  text-align: end;
}
.td-table tbody tr {
  border-bottom: 1px solid rgba(0,0,0,.05);
  transition: background .15s;
}
.td-table tbody tr:last-child { border-bottom: none; }
.td-table tbody tr:nth-child(even) { background: rgba(0,0,0,.02); }
.td-table tbody tr:hover { background: rgba(var(--accent-rgb, 240,170,31),.07); }

.td-rank {
  width: 60px;
  text-align: center;
  padding: 1rem .5rem;
}
.td-rank-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  font-weight: 700;
  font-size: .85rem;
}
.td-rank-badge.gold  { background: #FBE8AD; color: #8A5A00; }
.td-rank-badge.silver{ background: #E8E8E8; color: #555; }
.td-rank-badge.bronze{ background: #F5D9C5; color: #7A3F1A; }
.td-rank-badge.plain { background: rgba(0,0,0,.06); color: var(--color-ink-muted, #777); }

.td-name {
  padding: 1rem 1.25rem;
  font-weight: 600;
  color: var(--color-ink, #1a1a1a);
}
.td-name .td-anon-badge {
  display: inline-block;
  margin-inline-start: .4rem;
  font-size: .7rem;
  font-weight: 600;
  padding: .1rem .45rem;
  border-radius: 999px;
  background: rgba(0,0,0,.06);
  color: var(--color-ink-muted, #777);
  vertical-align: middle;
}

.td-amount {
  padding: 1rem 1.25rem;
  text-align: end;
  font-weight: 700;
  font-size: 1rem;
  color: var(--color-ink, #1a1a1a);
  white-space: nowrap;
  direction: ltr;
  unicode-bidi: isolate;
}
.td-amount .td-sar {
  display: inline-block;
  width: .9em;
  height: .9em;
  vertical-align: -.15em;
  fill: currentColor;
  margin-inline-end: .25rem;
}

/* Empty state */
.td-empty {
  text-align: center;
  padding: 4rem 1.5rem;
  color: var(--color-ink-muted, #777);
}
.td-empty svg {
  width: 48px;
  height: 48px;
  margin: 0 auto 1rem;
  opacity: .3;
}
.td-empty h3 {
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--color-ink, #1a1a1a);
  margin-bottom: .4rem;
}
.td-empty p { font-size: .9rem; margin-bottom: 1.25rem; }
.td-empty-cta {
  display: inline-block;
  padding: .65rem 1.5rem;
  border-radius: 8px;
  background: var(--accent, #F0AA1F);
  color: #fff;
  font-weight: 700;
  font-size: .9rem;
  text-decoration: none;
  transition: opacity .2s;
}
.td-empty-cta:hover { opacity: .88; }

/* note under table */
.td-note {
  text-align: center;
  font-size: .78rem;
  color: var(--color-ink-muted, #999);
  margin-top: 1.25rem;
  line-height: 1.6;
}

@media (prefers-color-scheme: dark) {
  body { background: #111621; color: #e8e8ec; }
  .td-hero { background: #161b26; border-bottom-color: rgba(255,255,255,.06); }
  .td-hero h1 { color: #f0f0f0; }
  .td-hero p  { color: #9a9fac; }
  .td-table { background: #1e2532; }
  .td-table-wrap { border-color: rgba(255,255,255,.08); box-shadow: 0 2px 16px rgba(0,0,0,.3); }
  .td-table thead tr { background: #1a1f2c; border-bottom-color: rgba(255,255,255,.08); }
  .td-table th { color: #9a9fac; }
  .td-table tbody tr { border-bottom-color: rgba(255,255,255,.05); }
  .td-table tbody tr:nth-child(even) { background: rgba(255,255,255,.03); }
  .td-table tbody tr:hover { background: rgba(240,170,31,.1); }
  .td-name { color: #e8e8ec; }
  .td-name .td-anon-badge { background: rgba(255,255,255,.1); color: #9a9fac; }
  .td-amount { color: #e8e8ec; }
  .td-rank-badge.plain { background: rgba(255,255,255,.08); color: #9a9fac; }
  .td-empty h3 { color: #e8e8ec; }
  .td-empty p, .td-note { color: #9a9fac; }
  footer { background: #111621; }
  .site.scrolled { background: #161b26; border-bottom-color: rgba(255,255,255,.06); }
  .site .links a { color: #c0c4ce; }
  .site .links a:hover { color: #f0f0f0; }
}
</style>
</head>
<body>
<a class="skip-link" href="#main-content">{{ __('welcome.skip_link') }}</a>

<!-- CSS-only mobile drawer toggle -->
<input type="checkbox" id="nav-drawer-toggle" aria-hidden="true" />
<label class="nav-drawer-overlay" for="nav-drawer-toggle" aria-hidden="true"></label>
<nav class="nav-drawer" aria-label="{{ __('welcome.nav.aria') }}">
  <label class="nav-drawer-close" for="nav-drawer-toggle" aria-label="{{ __('welcome.nav.close_drawer') }}">✕</label>
  <a href="{{ route('home') }}#idea">{{ __('welcome.nav.idea') }}</a>
  <a href="{{ route('home') }}#how">{{ __('welcome.nav.how') }}</a>
  <a href="{{ route('home') }}#trust">{{ __('welcome.nav.trust') }}</a>
  <a href="{{ route('home') }}#providers">{{ __('welcome.nav.providers') }}</a>
  <a href="{{ route('top-donors.index') }}" class="active">{{ __('welcome.nav.top_donors') }}</a>
  @if (auth()->check())
  <a href="{{ url('/dashboard') }}" class="cta danger nav-drawer-cta">{{ __('welcome.nav.dashboard') }}</a>
  @else
  <a href="{{ route('register') }}" class="cta accent nav-drawer-cta">{{ __('welcome.nav.give') }}</a>
  <a href="{{ route('login') }}" class="cta nav-drawer-cta--sm">{{ __('welcome.nav.login') }}</a>
  @endif
</nav>

<!-- ========== NAV ========== -->
<nav class="site scrolled" id="nav" aria-label="{{ __('welcome.nav.aria') }}">
  <div class="inner">
    <a href="{{ url('/') }}" class="logo" aria-label="{{ config('app.name', 'NUBL') }}">
      <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name', 'NUBL') }}" class="brand-logo" />
    </a>

    <div class="links">
      <a href="{{ route('home') }}#idea">{{ __('welcome.nav.idea') }}</a>
      <a href="{{ route('home') }}#how">{{ __('welcome.nav.how') }}</a>
      <a href="{{ route('home') }}#trust">{{ __('welcome.nav.trust') }}</a>
      <a href="{{ route('home') }}#providers">{{ __('welcome.nav.providers') }}</a>
      <a href="{{ route('top-donors.index') }}" style="color: var(--accent);">{{ __('welcome.nav.top_donors') }}</a>
      <button class="lang" id="langToggle" type="button" aria-label="{{ __('welcome.nav.lang_aria') }}" data-en-url="{{ route('locale.switch', 'en') }}" data-ar-url="{{ route('locale.switch', 'ar') }}">
        <span id="langLabel">{{ __('welcome.nav.lang_label') }}</span>
      </button>
      @if (auth()->check())
      <a href="{{ url('/dashboard') }}" class="cta danger">
        <span>{{ __('welcome.nav.dashboard') }}</span>
        <svg viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </a>
      @else
      <a href="{{ route('register') }}" class="cta accent">
        <span>{{ __('welcome.nav.give') }}</span>
        <svg viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </a>
      <a href="{{ route('login') }}" class="cta">
        <span>{{ __('welcome.nav.login') }}</span>
        <svg viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </a>
      @endif
    </div>

    <!-- Hamburger -->
    <label class="nav-hamburger" for="nav-drawer-toggle" aria-label="{{ __('welcome.nav.open_menu') }}" role="button" tabindex="0">
      <span></span><span></span><span></span>
    </label>
  </div>
</nav>

<!-- ========== MAIN ========== -->
<main id="main-content">

  <!-- Hero -->
  <section class="td-hero">
    <p class="eyebrow">{{ config('app.name', 'NUBL') }}</p>
    <h1>{{ __('top_donors.page_title') }}</h1>
    <p>{{ __('top_donors.page_subtitle') }}</p>
  </section>

  <!-- Table -->
  <div class="td-section">
    @if (count($donors) === 0)
      <div class="td-empty">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
          <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
        </svg>
        <h3>{{ __('top_donors.empty_title') }}</h3>
        <p>{{ __('top_donors.empty_body') }}</p>
        <a href="{{ route('register') }}" class="td-empty-cta">{{ __('top_donors.empty_cta') }}</a>
      </div>
    @else
      <div class="td-table-wrap" role="region" aria-label="{{ __('top_donors.page_title') }}">
        <table class="td-table">
          <thead>
            <tr>
              <th class="td-th-rank" scope="col">{{ __('top_donors.col_rank') }}</th>
              <th scope="col">{{ __('top_donors.col_donor') }}</th>
              <th class="td-th-amount" scope="col">{{ __('top_donors.col_total') }}</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($donors as $donor)
              <tr>
                <td class="td-rank">
                  @php
                    $badgeClass = match ($donor['rank']) {
                      1 => 'gold',
                      2 => 'silver',
                      3 => 'bronze',
                      default => 'plain',
                    };
                  @endphp
                  <span class="td-rank-badge {{ $badgeClass }}" aria-label="{{ __('top_donors.col_rank') }} {{ $donor['rank'] }}">
                    {{ $donor['rank'] }}
                  </span>
                </td>
                <td class="td-name">
                  {{ $donor['name'] }}
                  @if ($donor['is_anonymous'])
                    <span class="td-anon-badge" aria-label="{{ __('top_donors.anonymous_label') }}">
                      {{ __('top_donors.anonymous_label') }}
                    </span>
                  @endif
                </td>
                <td class="td-amount">
                  <svg class="td-sar" viewBox="0 0 1124.14 1256.39" aria-hidden="true" focusable="false">
                    <path d="M699.62,1113.02h0c-20.06,44.48-33.32,92.75-38.4,143.37l424.51-90.24c20.06-44.47,33.31-92.75,38.4-143.37l-424.51,90.24Z"/>
                    <path d="M1085.73,895.8c20.06-44.47,33.32-92.75,38.4-143.37l-330.68,70.33v-135.2l292.27-62.11c20.06-44.47,33.32-92.75,38.4-143.37l-330.68,70.27V66.13c-50.67,28.45-95.67,66.32-132.25,110.99v403.35l-132.25,28.11V0c-50.67,28.44-95.67,66.32-132.25,110.99v525.69l-295.91,62.88c-20.06,44.47-33.33,92.75-38.42,143.37l334.33-71.05v170.26l-358.3,76.14c-20.06,44.47-33.32,92.75-38.4,143.37l375.04-79.7c30.53-6.35,56.77-24.4,73.83-49.24l68.78-101.97v-.02c7.14-10.55,11.3-23.27,11.3-36.97v-149.98l132.25-28.11v270.4l424.53-90.28Z"/>
                  </svg>
                  {{ number_format($donor['total'], 0) }}
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <p class="td-note">
        {{ __('top_donors.anonymous_label') }} —
        {{ app()->getLocale() === 'ar'
            ? 'مجموع التبرعات المجهولة وتبرعات الزوار'
            : 'Combined total of anonymous and guest donations' }}
      </p>
    @endif
  </div>

</main>

<!-- ========== FOOTER ========== -->
<footer>
  <div class="wrap">
    <div class="rows">
      <div class="brand-col">
        <a href="{{ url('/') }}" class="logo" aria-label="{{ config('app.name', 'NUBL') }}">
          <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name', 'NUBL') }}" class="brand-logo" loading="lazy" />
        </a>
        <p>{{ __('welcome.footer.tagline') }}</p>
        <div class="social-row" role="list">
          <a href="#" class="social-icon" aria-label="Twitter / X" role="listitem">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 4l16 16M4 20L20 4"/></svg>
          </a>
          <a href="#" class="social-icon" aria-label="LinkedIn" role="listitem">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="4"/><path d="M7 10v7M7 7v.01M11 17v-4a2 2 0 0 1 4 0v4M11 13v4"/></svg>
          </a>
          <a href="#" class="social-icon" aria-label="Instagram" role="listitem">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg>
          </a>
        </div>
      </div>
      <div>
        <h5>{{ __('welcome.footer.platform') }}</h5>
        <a href="{{ route('home') }}#how">{{ __('welcome.footer.how') }}</a>
        <a href="{{ route('home') }}#trust">{{ __('welcome.footer.transparency') }}</a>
        <a href="{{ route('home') }}#providers">{{ __('welcome.footer.coverage') }}</a>
        <a href="{{ route('top-donors.index') }}">{{ __('welcome.nav.top_donors') }}</a>
      </div>
      <div>
        <h5>{{ __('welcome.footer.join') }}</h5>
        <a href="{{ route('register', ['type' => 'donor']) }}">{{ __('welcome.footer.support') }}</a>
        <a href="{{ route('register', ['type' => 'recipient']) }}">{{ __('welcome.footer.request') }}</a>
        <a href="{{ route('register.provider') }}">{{ __('welcome.footer.partner') }}</a>
      </div>
      <div>
        <h5>{{ __('welcome.footer.org') }}</h5>
        <a href="{{ route('home') }}#idea">{{ __('welcome.footer.about') }}</a>
        <a href="{{ route('home') }}#stories">{{ __('welcome.footer.press') }}</a>
        <a href="{{ route('home') }}#cta">{{ __('welcome.footer.contact') }}</a>
      </div>
    </div>
    <div class="bottom">
      <span>{{ __('welcome.footer.copy') }}</span>
      <span>{{ __('welcome.footer.legal') }}</span>
    </div>
  </div>
</footer>

<!-- ========== QUICK DONATION WIDGET ========== -->
<button class="qd-fab" id="qdFab" aria-label="{{ __('Quick Donation') }}">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
  {{ __('Quick Donation') }}
</button>
<div class="qd-overlay" id="qdOverlay"></div>
<div class="qd-panel" id="qdPanel">
  <div class="qd-header">
    <h3>{{ __('Quick Donation') }}</h3>
    <button class="qd-close" id="qdClose" aria-label="{{ __('Close') }}">&times;</button>
  </div>
  <div class="qd-body">
    <form method="POST" action="{{ route('guest.donation.initiate') }}" id="qdForm">
      @csrf
      <div class="qd-presets">
        <button type="button" class="qd-preset" data-amount="10">
          10
          <svg class="sar-icon" viewBox="0 0 1124.14 1256.39"><path d="M699.62,1113.02h0c-20.06,44.48-33.32,92.75-38.4,143.37l424.51-90.24c20.06-44.47,33.31-92.75,38.4-143.37l-424.51,90.24Z"/><path d="M1085.73,895.8c20.06-44.47,33.32-92.75,38.4-143.37l-330.68,70.33v-135.2l292.27-62.11c20.06-44.47,33.32-92.75,38.4-143.37l-330.68,70.27V66.13c-50.67,28.45-95.67,66.32-132.25,110.99v403.35l-132.25,28.11V0c-50.67,28.44-95.67,66.32-132.25,110.99v525.69l-295.91,62.88c-20.06,44.47-33.33,92.75-38.42,143.37l334.33-71.05v170.26l-358.3,76.14c-20.06,44.47-33.32,92.75-38.4,143.37l375.04-79.7c30.53-6.35,56.77-24.4,73.83-49.24l68.78-101.97v-.02c7.14-10.55,11.3-23.27,11.3-36.97v-149.98l132.25-28.11v270.4l424.53-90.28Z"/></svg>
        </button>
        <button type="button" class="qd-preset" data-amount="50">
          50
          <svg class="sar-icon" viewBox="0 0 1124.14 1256.39"><path d="M699.62,1113.02h0c-20.06,44.48-33.32,92.75-38.4,143.37l424.51-90.24c20.06-44.47,33.31-92.75,38.4-143.37l-424.51,90.24Z"/><path d="M1085.73,895.8c20.06-44.47,33.32-92.75,38.4-143.37l-330.68,70.33v-135.2l292.27-62.11c20.06-44.47,33.32-92.75,38.4-143.37l-330.68,70.27V66.13c-50.67,28.45-95.67,66.32-132.25,110.99v403.35l-132.25,28.11V0c-50.67,28.44-95.67,66.32-132.25,110.99v525.69l-295.91,62.88c-20.06,44.47-33.33,92.75-38.42,143.37l334.33-71.05v170.26l-358.3,76.14c-20.06,44.47-33.32,92.75-38.4,143.37l375.04-79.7c30.53-6.35,56.77-24.4,73.83-49.24l68.78-101.97v-.02c7.14-10.55,11.3-23.27,11.3-36.97v-149.98l132.25-28.11v270.4l424.53-90.28Z"/></svg>
        </button>
        <button type="button" class="qd-preset" data-amount="100">
          100
          <svg class="sar-icon" viewBox="0 0 1124.14 1256.39"><path d="M699.62,1113.02h0c-20.06,44.48-33.32,92.75-38.4,143.37l424.51-90.24c20.06-44.47,33.31-92.75,38.4-143.37l-424.51,90.24Z"/><path d="M1085.73,895.8c20.06-44.47,33.32-92.75,38.4-143.37l-330.68,70.33v-135.2l292.27-62.11c20.06-44.47,33.32-92.75,38.4-143.37l-330.68,70.27V66.13c-50.67,28.45-95.67,66.32-132.25,110.99v403.35l-132.25,28.11V0c-50.67,28.44-95.67,66.32-132.25,110.99v525.69l-295.91,62.88c-20.06,44.47-33.33,92.75-38.42,143.37l334.33-71.05v170.26l-358.3,76.14c-20.06,44.47-33.32,92.75-38.4,143.37l375.04-79.7c30.53-6.35,56.77-24.4,73.83-49.24l68.78-101.97v-.02c7.14-10.55,11.3-23.27,11.3-36.97v-149.98l132.25-28.11v270.4l424.53-90.28Z"/></svg>
        </button>
      </div>
      <div class="qd-amount-wrap">
        <label for="qdAmountInput">{{ __('Donation Amount') }}</label>
        <svg class="qd-amount-prefix" viewBox="0 0 1124.14 1256.39"><path d="M699.62,1113.02h0c-20.06,44.48-33.32,92.75-38.4,143.37l424.51-90.24c20.06-44.47,33.31-92.75,38.4-143.37l-424.51,90.24Z"/><path d="M1085.73,895.8c20.06-44.47,33.32-92.75,38.4-143.37l-330.68,70.33v-135.2l292.27-62.11c20.06-44.47,33.32-92.75,38.4-143.37l-330.68,70.27V66.13c-50.67,28.45-95.67,66.32-132.25,110.99v403.35l-132.25,28.11V0c-50.67,28.44-95.67,66.32-132.25,110.99v525.69l-295.91,62.88c-20.06,44.47-33.33,92.75-38.42,143.37l334.33-71.05v170.26l-358.3,76.14c-20.06,44.47-33.32,92.75-38.4,143.37l375.04-79.7c30.53-6.35,56.77-24.4,73.83-49.24l68.78-101.97v-.02c7.14-10.55,11.3-23.27,11.3-36.97v-149.98l132.25-28.11v270.4l424.53-90.28Z"/></svg>
        <input type="number" name="amount" id="qdAmountInput" class="qd-amount-input"
               placeholder="{{ __('Donation Amount') }}" step="0.01" min="1" max="999999.99" required>
      </div>
      <div class="qd-error" id="qdError"></div>
      <div class="qd-info">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
        <span>{{ __('Your donation will automatically go to the most needy cases') }}</span>
      </div>
      <div class="qd-payment-icons">
        <img src="{{ asset('images/icons/visa-icon.svg') }}" alt="Visa">
        <img src="{{ asset('images/icons/apple-icon.svg') }}" alt="Apple Pay">
        <img src="{{ asset('images/icons/mastercard-icon.svg') }}" alt="Mastercard">
        <img src="{{ asset('images/icons/mada-icon.svg') }}" alt="Mada">
      </div>
      <button type="submit" class="qd-submit" id="qdSubmit">{{ __('Donate Now') }}</button>
    </form>
  </div>
</div>

<script>
(function() {
  const CURRENT_LOCALE = "{{ app()->getLocale() }}";
  const fab      = document.getElementById('qdFab');
  const overlay  = document.getElementById('qdOverlay');
  const panel    = document.getElementById('qdPanel');
  const closeBtn = document.getElementById('qdClose');
  const form     = document.getElementById('qdForm');
  const input    = document.getElementById('qdAmountInput');
  const errorEl  = document.getElementById('qdError');
  const presets  = panel.querySelectorAll('.qd-preset');
  const submitBtn= document.getElementById('qdSubmit');

  function open() {
    fab.classList.add('hidden');
    overlay.classList.add('on');
    panel.style.display = 'block';
    requestAnimationFrame(function() {
      requestAnimationFrame(function() {
        panel.classList.add('on');
      });
    });
  }

  function close() {
    panel.classList.remove('on');
    overlay.classList.remove('on');
    setTimeout(function() { panel.style.display = 'none'; fab.classList.remove('hidden'); }, 400);
  }

  fab.addEventListener('click', open);
  overlay.addEventListener('click', close);
  closeBtn.addEventListener('click', close);
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && panel.classList.contains('on')) close();
  });

  presets.forEach(function(btn) {
    btn.addEventListener('click', function() {
      var val = btn.getAttribute('data-amount');
      input.value = val;
      presets.forEach(function(b) { b.classList.remove('selected'); });
      btn.classList.add('selected');
      errorEl.classList.remove('show');
    });
  });

  input.addEventListener('input', function() {
    presets.forEach(function(b) {
      b.classList.toggle('selected', b.getAttribute('data-amount') === input.value);
    });
    errorEl.classList.remove('show');
  });

  form.addEventListener('submit', function(e) {
    var val = parseFloat(input.value);
    if (!input.value || isNaN(val) || val < 1) {
      e.preventDefault();
      errorEl.textContent = '{{ __("Please enter a valid donation amount (minimum 1 SAR).") }}';
      errorEl.classList.add('show');
      input.focus();
      return;
    }
    submitBtn.disabled = true;
    submitBtn.textContent = '{{ __("Processing...") }}';
  });

  // Language toggle
  const langToggle = document.getElementById('langToggle');
  if (langToggle) {
    langToggle.addEventListener('click', () => {
      const next = CURRENT_LOCALE === 'en' ? 'ar' : 'en';
      const target = next === 'ar' ? langToggle.dataset.arUrl : langToggle.dataset.enUrl;
      if (target) window.location.href = target;
    });
  }
})();
</script>
</body>
</html>
