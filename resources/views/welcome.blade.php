<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<meta name="csrf-token" content="{{ csrf_token() }}" />
<meta name="description" content="NUBL connects donors, recipients, and local food providers through dignified, private food support." />
<meta name="theme-color" content="#F5F4F1" />
<title>{{ config('app.name', 'NUBL') }} — Food support with dignity</title>
<meta property="og:site_name" content="{{ config('app.name', 'NUBL') }}" />
<script type="application/ld+json">
@verbatim
{
  "@context": "https://schema.org",
  "@type": "WebSite",
  "name": "NUBL",
  "alternateName": "NUBL Platform",
  "url": "https://nubl.faljaroshah.me"
}
@endverbatim
</script>

@include('partials.favicons')

@vite(['resources/css/welcome-landing.css'])

</head>
<body>
<a class="skip-link" href="#main-content">{{ __('welcome.skip_link') }}</a>

<!-- CSS-only mobile drawer toggle -->
<input type="checkbox" id="nav-drawer-toggle" aria-hidden="true" />
<label class="nav-drawer-overlay" for="nav-drawer-toggle" aria-hidden="true"></label>
<nav class="nav-drawer" aria-label="{{ __('welcome.nav.aria') }}">
  <label class="nav-drawer-close" for="nav-drawer-toggle" aria-label="{{ __('welcome.nav.close_drawer') }}">✕</label>

  <div class="nav-drawer-links">
    <a href="#idea">{{ __('welcome.nav.idea') }}</a>
    <a href="#how">{{ __('welcome.nav.how') }}</a>
    <a href="#trust">{{ __('welcome.nav.trust') }}</a>
    <a href="#providers">{{ __('welcome.nav.providers') }}</a>
    <a href="{{ route('top-donors.index') }}">{{ __('welcome.nav.top_donors') }}</a>
  </div>

  <div class="nav-drawer-actions">
    @if (auth()->check())
    <a href="{{ url('/dashboard') }}" class="cta accent nav-drawer-cta">{{ __('welcome.nav.dashboard') }}</a>
    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="cta ghost nav-drawer-cta">{{ __('Logout') }}</button>
    </form>
    @else
    <a href="{{ route('register') }}" class="cta accent nav-drawer-cta">{{ __('welcome.nav.give') }}</a>
    <a href="{{ route('login') }}" class="cta nav-drawer-cta">{{ __('welcome.nav.login') }}</a>
    @endif
  </div>

  <div class="nav-drawer-footer">
    <button class="nav-drawer-lang" type="button" onclick="window.location.href='{{ app()->getLocale() === 'ar' ? route('locale.switch', 'en') : route('locale.switch', 'ar') }}'">{{ __('welcome.nav.lang_label') }}</button>
  </div>
</nav>

<!-- ========== NAV ========== -->
<nav class="site" id="nav" aria-label="{{ __('welcome.nav.aria') }}">
  <div class="inner">
    <a href="{{ url('/') }}" class="logo" aria-label="{{ config('app.name', 'NUBL') }}">
      <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name', 'NUBL') }}" class="brand-logo" />
    </a>

    <div class="links">
      <a href="#idea">{{ __('welcome.nav.idea') }}</a>
      <a href="#how">{{ __('welcome.nav.how') }}</a>
      <a href="#trust">{{ __('welcome.nav.trust') }}</a>
      <a href="#providers">{{ __('welcome.nav.providers') }}</a>
      <a href="{{ route('top-donors.index') }}">{{ __('welcome.nav.top_donors') }}</a>
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

    <!-- Mobile-only: lang + hamburger -->
    <div class="nav-mobile-end">
      <button class="lang-mobile" type="button" aria-label="{{ __('welcome.nav.lang_aria') }}" onclick="window.location.href='{{ app()->getLocale() === 'ar' ? route('locale.switch', 'en') : route('locale.switch', 'ar') }}'">{{ __('welcome.nav.lang_label') }}</button>
      <label class="nav-hamburger" for="nav-drawer-toggle" aria-label="{{ __('welcome.nav.open_menu') }}" role="button" tabindex="0">
        <span></span><span></span><span></span>
      </label>
    </div>
  </div>
</nav>

<main id="main-content">
<!-- ========== HERO ========== -->
<header class="hero" id="top">
  <div class="sigil" aria-hidden="true">
    <svg viewBox="0 0 400 500" preserveAspectRatio="xMidYMid slice">
      <defs>
        <pattern id="dots" x="0" y="0" width="24" height="24" patternUnits="userSpaceOnUse">
          <circle cx="1" cy="1" r="1" fill="#C98C0A" opacity="0.35"/>
        </pattern>
      </defs>
      <rect width="400" height="500" fill="url(#dots)"/>
      <circle cx="280" cy="200" r="140" fill="none" stroke="#F0AA1F" stroke-width="1" opacity="0.4"/>
      <circle cx="280" cy="200" r="90" fill="none" stroke="#E0305E" stroke-width="1" opacity="0.3"/>
      <circle cx="280" cy="200" r="40" fill="#F0AA1F" opacity="0.15"/>
    </svg>
  </div>

  <div class="wrap hero-grid">
    <div>
      <span class="eyebrow reveal">{{ __('welcome.hero.eyebrow') }}</span>
      <h1 class="display reveal mt-hero-head">
        {{ __('welcome.hero.h1_1') }} <i>{{ __('welcome.hero.h1_2') }}</i> {{ __('welcome.hero.h1_3') }}
      </h1>
      <p class="sub reveal">{!! __('welcome.hero.sub') !!}</p>
      <div class="hero-ctas reveal">
        <a href="{{ route('register', ['type' => 'donor']) }}" class="cta accent">
          <span>{{ __('welcome.hero.cta1') }}</span>
          <svg viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
        <a href="{{ route('register', ['type' => 'recipient']) }}" class="cta ghost">
          <span>{{ __('welcome.hero.cta2') }}</span>
          <svg viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
      </div>
    </div>

    <div class="reveal hero-impact hero-impact--delayed">
      <div class="agg-card">
        <div class="agg-head">
          <span class="stamp"><span class="dot"></span><span>{{ __('welcome.impact.live') }}</span></span>
          <span class="stamp">{{ __('welcome.impact.q') }}</span>
        </div>
        <div class="agg-big" data-counter="{{ $heroStats['totalDelivered'] }}"><span class="agg-value">0</span><span class="unit">{{ __('welcome.impact.amount_unit') }}</span></div>
        <div class="agg-label">{{ __('welcome.impact.amount_label') }}</div>

        <div class="agg-split">
          <div>
            <div class="num" data-counter="{{ $heroStats['familiesSupported'] }}">0</div>
            <div class="tag">{{ __('welcome.impact.families_tag') }}</div>
          </div>
          <div>
            <div class="num" data-counter="{{ $heroStats['localProviders'] }}">0</div>
            <div class="tag">{{ __('welcome.impact.providers_tag') }}</div>
          </div>
        </div>
      </div>

      <!-- aria-live="polite" so screen readers announce new feed items non-intrusively -->
      <div class="feed" aria-live="polite" aria-atomic="false" aria-relevant="additions">
        <div class="feed-head">
          <span class="live"><span class="dot"></span><span>{{ __('welcome.impact.live_feed') }}</span></span>
          <span>{{ __('welcome.impact.anonymized') }}</span>
        </div>
        <div class="feed-list" id="feedList"></div>
      </div>
    </div>
  </div>
</header>

<!-- ========== MANIFESTO / IDEA ========== -->
<section class="manifesto" id="idea">
  <div class="wrap manifesto-grid">
    <aside class="manifesto-aside">
      {{ __('welcome.manifesto.chapter') }}
      <hr/>
      {{ __('welcome.manifesto.chapter_label') }}
    </aside>
    <div>
      <span class="eyebrow">{{ __('welcome.manifesto.eyebrow') }}</span>
      <h2 class="display mt-section-head">
        {{ __('welcome.manifesto.h1') }} <em>{{ __('welcome.manifesto.h2') }}</em> {{ __('welcome.manifesto.h3') }}
      </h2>
      <p class="lede">{{ __('welcome.manifesto.p') }}</p>
    </div>
  </div>
</section>

<!-- ========== HOW IT WORKS ========== -->
<section class="flow" id="how">
  <div class="wrap">
    <div class="flow-head">
      <div>
        <span class="eyebrow">{{ __('welcome.how.chapter') }}</span>
        <h2 class="display mt-flow-head">
          {{ __('welcome.how.h1') }} <i>{{ __('welcome.how.h2') }}</i>
        </h2>
      </div>
      <p>{{ __('welcome.how.p') }}</p>
    </div>

    <div class="flow-diagram">
      <div class="flow-connector" aria-hidden="true"></div>
      <div class="flow-track">

        <article class="role-card supporter reveal">
          <span class="accent-dot"></span>
          <span class="step">{{ __('welcome.how.step1') }}</span>
          <div class="icon">
            <svg width="28" height="28" viewBox="0 0 28 28" fill="none">
              <path d="M14 25S4 18 4 11a6 6 0 0 1 10-4.5A6 6 0 0 1 24 11c0 7-10 14-10 14z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
            </svg>
          </div>
          <h3>{{ __('welcome.how.role1_h') }}</h3>
          <p class="role-body">{{ __('welcome.how.role1_p') }}</p>
          <div class="role-actions">
            <a href="{{ route('register', ['type' => 'donor']) }}"><span>{{ __('welcome.how.role1_cta') }}</span>
              <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
          </div>
        </article>

        <article class="role-card provider reveal">
          <span class="accent-dot"></span>
          <span class="step">{{ __('welcome.how.step2') }}</span>
          <div class="icon">
            <svg width="28" height="28" viewBox="0 0 28 28" fill="none">
              <path d="M5 12.5h18V24H5V12.5z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
              <path d="M4 12.5l2.5-5.5H21.5L24 12.5" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
              <path d="M6.5 7v5.5M10 7v5.5M14 7v5.5M18 7v5.5M21.5 7v5.5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
              <path d="M11 24V16h6v8" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
            </svg>
          </div>
          <h3>{{ __('welcome.how.role2_h') }}</h3>
          <p class="role-body">{{ __('welcome.how.role2_p') }}</p>
          <div class="role-actions">
            <a href="{{ route('register.provider') }}"><span>{{ __('welcome.how.role2_cta') }}</span>
              <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
          </div>
        </article>

        <article class="role-card recipient reveal">
          <span class="accent-dot"></span>
          <span class="step">{{ __('welcome.how.step3') }}</span>
          <div class="icon">
            <svg width="28" height="28" viewBox="0 0 28 28" fill="none">
              <circle cx="14" cy="10" r="4" stroke="currentColor" stroke-width="1.6"/>
              <path d="M5 24c0-4.5 4-8 9-8s9 3.5 9 8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
            </svg>
          </div>
          <h3>{{ __('welcome.how.role3_h') }}</h3>
          <p class="role-body">{{ __('welcome.how.role3_p') }}</p>
          <div class="role-actions">
            <a href="{{ route('register', ['type' => 'recipient']) }}"><span>{{ __('welcome.how.role3_cta') }}</span>
              <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
          </div>
        </article>

      </div>
    </div>
  </div>
</section>

<!-- ========== PRIVACY ========== -->
<section class="privacy" id="privacy">
  <div class="wrap privacy-grid">
    <div>
      <span class="eyebrow">{{ __('welcome.privacy.eyebrow') }}</span>
      <h2 class="display mt-section-head">
        {{ __('welcome.privacy.h1') }}<br/>
        <em>{{ __('welcome.privacy.h2') }}</em>
      </h2>
      <p class="body">{{ __('welcome.privacy.p') }}</p>
      <div class="privacy-pills">
        <span class="pill">{{ __('welcome.privacy.pill1') }}</span>
        <span class="pill">{{ __('welcome.privacy.pill2') }}</span>
        <span class="pill">{{ __('welcome.privacy.pill4') }}</span>
      </div>
    </div>

    <div class="card-pair reveal">
      <div class="id-card clear">
        <div class="title-line">
          <span>{{ __('welcome.privacy.id_held') }}</span>
          <span>01 · RAW</span>
        </div>
        <div class="id-row id-row--first">
          <span class="k">{{ __('welcome.privacy.id_name') }}</span>
          <span class="v redacted"></span>
        </div>
        <div class="id-row">
          <span class="k">{{ __('welcome.privacy.id_area') }}</span>
          <span class="v redacted"></span>
        </div>
        <div class="id-row">
          <span class="k">{{ __('welcome.privacy.id_family') }}</span>
          <span class="v redacted"></span>
        </div>
        <div class="id-row">
          <span class="k">{{ __('welcome.privacy.id_status') }}</span>
          <span class="v verified">{{ __('welcome.privacy.id_verified') }}</span>
        </div>
      </div>

      <div class="arrow-down">
        <svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true"><path d="M7 2v10M3 8l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        <span>{{ __('welcome.privacy.tokenized') }}</span>
      </div>

      <div class="id-card protected">
        <div class="title-line">
          <span>{{ __('welcome.privacy.id_shown') }}</span>
          <span>02 · TOKEN</span>
        </div>
        <div class="id-row id-row--first">
          <span class="k">{{ __('welcome.privacy.id_ref') }}</span>
          <span class="v token">NBL-7F3A-QR</span>
        </div>
        <div class="id-row">
          <span class="k">{{ __('welcome.privacy.id_region') }}</span>
          <span class="v">{{ __('welcome.privacy.id_region_v') }}</span>
        </div>
        <div class="id-row">
          <span class="k">{{ __('welcome.privacy.id_household') }}</span>
          <span class="v">{{ __('welcome.privacy.id_household_v') }}</span>
        </div>
        <div class="id-row">
          <span class="k">{{ __('welcome.privacy.id_fulfillment') }}</span>
          <span class="v">{{ __('welcome.privacy.id_fulfill_v') }}</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ========== TRUST / TRANSPARENCY ========== -->
<section class="trust" id="trust">
  <div class="wrap trust-grid">
    <div class="trust-left">
      <span class="eyebrow">{{ __('welcome.trust.eyebrow') }}</span>
      <h2 class="display mt-section-head">
        {{ __('welcome.trust.h1') }} <i>{{ __('welcome.trust.h2') }}</i>
      </h2>
      <p>{{ __('welcome.trust.p') }}</p>

      @if($heroStats['trustBadges'] !== null)
      <div class="trust-badges">
        <div class="badge-card">
          <div class="n">{{ $heroStats['trustBadges']['delivered'] }}<span class="badge-pct">%</span></div>
          <div class="t">{{ __('welcome.trust.badge1') }}</div>
        </div>
        <div class="badge-card">
          <div class="n">{{ $heroStats['trustBadges']['held'] }}<span class="badge-pct">%</span></div>
          <div class="t">{{ __('welcome.trust.badge2') }}</div>
        </div>
      </div>
      @endif
    </div>

    <div class="transparency-ledger" aria-label="Live ledger preview">
      <div class="ledger-head">
        <h4>{{ $heroStats['trustLedger']['is_live'] ? __('welcome.trust.ledger_h') : __('welcome.trust.ledger_h_preview') }}</h4>
        <span class="date" id="ledgerDate">19 APR 2026</span>
      </div>
      @foreach($heroStats['trustLedger']['rows'] as $row)
      <div class="ledger-row">
        <div>
          <div class="desc">{{ $row['desc'] }}</div>
          @if($heroStats['trustLedger']['is_live'])
          <div class="meta">{{ $row['meta'] }}</div>
          @endif
        </div>
        <div class="amt">{{ $row['amount'] }}<span class="u">{{ __('welcome.trust.sar') }}</span></div>
      </div>
      @endforeach

      <div class="ledger-foot">
        <span class="ledger-foot-meta">
          @if($heroStats['trustLedger']['is_live'])
            {{ __('welcome.trust.ledger_count_live', ['shown' => $heroStats['trustLedger']['shown'], 'total' => $heroStats['trustLedger']['total']]) }}
          @else
            {{ __('welcome.trust.ledger_count_preview') }}
          @endif
        </span>
        <a href="#trust">{{ __('welcome.trust.ledger_full') }}</a>
      </div>
    </div>
  </div>
</section>

<!-- ========== PROVIDERS ========== -->
<section class="providers" id="providers">
  <div class="wrap">
    <div class="providers-intro">
      <span class="eyebrow">{{ __('welcome.providers.eyebrow') }}</span>
      <h2 class="display mt-section-head">
        {{ __('welcome.providers.h1') }} <em>{{ __('welcome.providers.h2') }}</em>
      </h2>
      <p>{{ __('welcome.providers.p') }}</p>
    </div>

    <div class="provider-types">
      <div class="provider-cell reveal">
        <div class="glyph">
          <svg width="36" height="36" viewBox="0 0 36 36" fill="none">
            <rect x="5" y="10" width="26" height="20" stroke="currentColor" stroke-width="1.5"/>
            <path d="M5 14h26" stroke="currentColor" stroke-width="1.5"/>
            <path d="M13 10V6h10v4" stroke="currentColor" stroke-width="1.5"/>
            <circle cx="13" cy="22" r="1.5" fill="currentColor"/>
            <circle cx="23" cy="22" r="1.5" fill="currentColor"/>
          </svg>
        </div>
        <h4>{{ __('welcome.providers.prov1_h') }}</h4>
        <p>{{ __('welcome.providers.prov1_p') }}</p>
        <div class="cnt"><b data-counter-inline="{{ $heroStats['providerCounts']['grocery'] }}">{{ $heroStats['providerCounts']['grocery'] }}</b><span>{{ __('welcome.providers.prov1_cnt') }}</span></div>
      </div>

      <div class="provider-cell reveal">
        <div class="glyph">
          <svg width="36" height="36" viewBox="0 0 36 36" fill="none">
            <path d="M10 6v12a4 4 0 0 0 4 4v8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            <path d="M10 6v12M14 6v12M18 6v12a4 4 0 0 1-4 4" stroke="currentColor" stroke-width="1.5"/>
            <path d="M24 6c-2 0-3 4-3 8s1 5 3 5v11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
          </svg>
        </div>
        <h4>{{ __('welcome.providers.prov4_h') }}</h4>
        <p>{{ __('welcome.providers.prov4_p') }}</p>
        <div class="cnt"><b data-counter-inline="{{ $heroStats['providerCounts']['restaurant'] }}">{{ $heroStats['providerCounts']['restaurant'] }}</b><span>{{ __('welcome.providers.prov4_cnt') }}</span></div>
      </div>

      <div class="provider-cell reveal">
        <div class="glyph">
          <svg width="36" height="36" viewBox="0 0 36 36" fill="none">
            <path d="M6 28h24M8 28V14c0-4 4-8 10-8s10 4 10 8v14" stroke="currentColor" stroke-width="1.5"/>
            <path d="M13 14h10" stroke="currentColor" stroke-width="1.5"/>
          </svg>
        </div>
        <h4>{{ __('welcome.providers.prov2_h') }}</h4>
        <p>{{ __('welcome.providers.prov2_p') }}</p>
        <div class="cnt"><b data-counter-inline="{{ $heroStats['providerCounts']['catering'] }}">{{ $heroStats['providerCounts']['catering'] }}</b><span>{{ __('welcome.providers.prov2_cnt') }}</span></div>
      </div>



      <div class="provider-cell reveal">
        <div class="glyph">
          <svg width="36" height="36" viewBox="0 0 36 36" fill="none">
            <path d="M6 22c0-6 5-10 12-10s12 4 12 10H6z" stroke="currentColor" stroke-width="1.5"/>
            <path d="M4 26h28" stroke="currentColor" stroke-width="1.5"/>
            <path d="M14 8v4M18 6v6M22 8v4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
          </svg>
        </div>
        <h4>{{ __('welcome.providers.prov3_h') }}</h4>
        <p>{{ __('welcome.providers.prov3_p') }}</p>
        <div class="cnt"><b data-counter-inline="{{ $heroStats['providerCounts']['bakery'] }}">{{ $heroStats['providerCounts']['bakery'] }}</b><span>{{ __('welcome.providers.prov3_cnt') }}</span></div>
      </div>


    </div>

    <div class="provider-strip">
      <div class="big display">
        {{ __('welcome.providers.strip_1') }} <em>{{ __('welcome.providers.strip_2') }}</em>
      </div>
      <a href="{{ route('register.provider') }}" class="cta">
        <span>{{ __('welcome.providers.strip_cta') }}</span>
        <svg viewBox="0 0 16 16" width="14" height="14" fill="none" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </a>
    </div>
  </div>
</section>

<!-- ========== STORIES ========== -->
<section class="stories" id="stories">
  <div class="wrap">
    <div class="stories-head">
      <div>
        <span class="eyebrow">{{ __('welcome.stories.eyebrow') }}</span>
        <h2 class="display mt-flow-head">
          {{ __('welcome.stories.h1') }} <i>{{ __('welcome.stories.h2') }}</i>
        </h2>
      </div>
      <span class="mono-caption">{{ __('welcome.stories.consent') }}</span>
    </div>

    <div class="stories-grid">
      <article class="story feature reveal">
        <p class="quote">{{ __('welcome.stories.story1') }}</p>
        <div class="story-meta">
          <span class="role">{{ __('welcome.stories.story1_role') }}</span>
          <span>{{ __('welcome.stories.story1_loc') }}</span>
        </div>
      </article>

      <article class="story reveal">
        <p class="quote">{{ __('welcome.stories.story2') }}</p>
        <div class="story-meta">
          <span class="role">{{ __('welcome.stories.story2_role') }}</span>
          <span>{{ __('welcome.stories.story2_loc') }}</span>
        </div>
      </article>

      <article class="story feature-2 reveal">
        <p class="quote">{{ __('welcome.stories.story3') }}</p>
        <div class="story-meta">
          <span class="role">{{ __('welcome.stories.story3_role') }}</span>
          <span>{{ __('welcome.stories.story3_loc') }}</span>
        </div>
      </article>
    </div>
  </div>
</section>

{{-- CTA BAND section hidden --}}

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
        <!-- Social icons -->
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
        <a href="#how">{{ __('welcome.footer.how') }}</a>
        <a href="#trust">{{ __('welcome.footer.transparency') }}</a>
        <a href="#providers">{{ __('welcome.footer.coverage') }}</a>
      </div>
      <div>
        <h5>{{ __('welcome.footer.join') }}</h5>
        <a href="{{ route('register', ['type' => 'donor']) }}">{{ __('welcome.footer.support') }}</a>
        <a href="{{ route('register', ['type' => 'recipient']) }}">{{ __('welcome.footer.request') }}</a>
        <a href="{{ route('register.provider') }}">{{ __('welcome.footer.partner') }}</a>
      </div>
      <div>
        <h5>{{ __('welcome.footer.org') }}</h5>
        <a href="#idea">{{ __('welcome.footer.about') }}</a>
        <a href="#stories">{{ __('welcome.footer.press') }}</a>
        <a href="#cta">{{ __('welcome.footer.contact') }}</a>
      </div>
    </div>
    <div class="bottom">
      <span>{{ __('welcome.footer.copy') }}</span>
      <span>{{ __('welcome.footer.legal') }}</span>
    </div>
  </div>
</footer>

<!-- ========== TWEAKS ========== -->
<div id="tweaks-root">
  <div class="tweaks-window">
    <div class="tweaks-head">
      <span>Tweaks</span>
      <span id="tweakStatus">·</span>
    </div>
    <div class="tweaks-body">
      <div class="tweak-row">
        <label>Language</label>
        <div class="opts" data-tweak="lang">
          <button data-val="en" class="active">English</button>
          <button data-val="ar">العربية</button>
        </div>
      </div>
      <div class="tweak-row">
        <label>Primary accent</label>
        <div class="opts" data-tweak="accent">
          <button data-val="gold" class="active">Gold</button>
          <button data-val="rose">Rose</button>
          <button data-val="navy">Navy</button>
        </div>
      </div>
      <div class="tweak-row">
        <label>Density</label>
        <div class="opts" data-tweak="density">
          <button data-val="compact">Compact</button>
          <button data-val="default" class="active">Balanced</button>
          <button data-val="airy">Airy</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  // -------- Close mobile drawer on link click --------
  (function() {
    const toggle = document.getElementById('nav-drawer-toggle');
    document.querySelectorAll('.nav-drawer-links a').forEach(function(link) {
      link.addEventListener('click', function() { toggle.checked = false; });
    });
  })();

  // -------- Locale (server-side) --------
  const CURRENT_LOCALE = "{{ app()->getLocale() }}";
  const FEED_POLL_URL = @json(route('landing.feed'));
  const FEED_POLL_MS = 20000;
  let FEED_ITEMS = @json($heroStats['feedItems']);

  // -------- Tweak defaults (editmode block) --------
  const TWEAK_DEFAULTS = /*EDITMODE-BEGIN*/{
    "lang": "{{ app()->getLocale() === 'ar' ? 'ar' : 'en' }}",
    "accent": "gold",
    "density": "default"
  }/*EDITMODE-END*/;

  let TWEAKS = { ...TWEAK_DEFAULTS };
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // -------- Apply accent --------
  function applyAccent(name) {
    const map = {
      gold:  { a: '#F0AA1F', adk: '#C98C0A', alt: '#FBE8AD', abg: '#FEF9EC' },
      rose:  { a: '#E0305E', adk: '#B8244C', alt: '#F7B8CB', abg: '#FEF0F4' },
      navy:  { a: '#293765', adk: '#1C2747', alt: '#A8B3D0', abg: '#F0F3FA' }
    };
    const c = map[name] || map.gold;
    const r = document.documentElement.style;
    r.setProperty('--accent', c.a);
    r.setProperty('--accent-dk', c.adk);
    r.setProperty('--accent-lt', c.alt);
    r.setProperty('--accent-bg', c.abg);
  }

  // -------- Apply density --------
  function applyDensity(d) {
    document.documentElement.setAttribute('data-density', d);
  }

  // -------- Live feed cycling --------
  let feedIdx = 0;
  let feedTimer = null;
  async function refreshFeedFromServer() {
    if (document.hidden) return;
    try {
      const r = await fetch(FEED_POLL_URL, {
        headers: { Accept: 'application/json' },
        credentials: 'same-origin',
      });
      if (!r.ok) return;
      const data = await r.json();
      if (!data.items || !Array.isArray(data.items)) return;
      FEED_ITEMS = data.items;
      renderFeed();
      updateHeroCounters(data);
    } catch (_) { /* keep previous items */ }
  }

  function updateHeroCounters(data) {
    const splitCounters = document.querySelectorAll('.agg-split .num[data-counter]');
    const map = {
      totalDelivered: document.querySelector('.agg-big[data-counter]'),
      familiesSupported: splitCounters[0] || null,
      localProviders: splitCounters[1] || null,
    };
    Object.entries(map).forEach(([key, el]) => {
      if (!el || data[key] == null) return;
      const newVal = parseInt(data[key], 10);
      const oldVal = parseInt(el.dataset.counter, 10);
      if (newVal === oldVal) return;
      el.dataset.counter = newVal;
      animateCounterFrom(el, oldVal, newVal);
    });
  }

  function animateCounterFrom(el, from, to) {
    const duration = 900;
    const start = performance.now();
    const locale = CURRENT_LOCALE === 'ar' ? 'ar-EG' : 'en-US';
    const valueEl = el.querySelector(':scope > .agg-value');
    const textNode = valueEl ? null : el.childNodes[el.childNodes.length - 1];
    function setFormatted(n) {
      const s = Math.floor(n).toLocaleString(locale);
      if (valueEl) valueEl.textContent = s;
      else textNode.nodeValue = s;
    }
    if (reduceMotion) { setFormatted(to); return; }
    function tick(now) {
      const t = Math.min(1, (now - start) / duration);
      const eased = 1 - Math.pow(1 - t, 3);
      setFormatted(from + (to - from) * eased);
      if (t < 1) requestAnimationFrame(tick);
      else setFormatted(to);
    }
    requestAnimationFrame(tick);
  }

  function renderFeed() {
    const list = document.getElementById('feedList');
    if (!list) return;
    list.innerHTML = '';
    FEED_ITEMS.forEach((it, i) => {
      const d = document.createElement('div');
      d.className = 'feed-item' + (i === 0 ? ' active' : '');
      const r1 = document.createElement('div');
      r1.className = 'row1';
      r1.textContent = it.row1;
      const r2 = document.createElement('div');
      r2.className = 'row2';
      r2.textContent = it.row2;
      d.appendChild(r1);
      d.appendChild(r2);
      list.appendChild(d);
    });
    feedIdx = 0;
    if (feedTimer) clearInterval(feedTimer);
    if (reduceMotion) return;
    feedTimer = setInterval(() => {
      const all = list.querySelectorAll('.feed-item');
      if (!all.length) return;
      all[feedIdx].classList.remove('active');
      feedIdx = (feedIdx + 1) % all.length;
      all[feedIdx].classList.add('active');
    }, 3500);
  }

  // -------- Counter animation --------
  function animateCounter(el, target) {
    const duration = 1800;
    const start = performance.now();
    const locale = CURRENT_LOCALE === 'ar' ? 'ar-EG' : 'en-US';
    const valueEl = el.querySelector(':scope > .agg-value');
    const textNode = valueEl ? null : el.childNodes[el.childNodes.length - 1];
    function setFormatted(n) {
      const s = n.toLocaleString(locale);
      if (valueEl) valueEl.textContent = s;
      else textNode.nodeValue = s;
    }
    if (reduceMotion) {
      setFormatted(target);
      return;
    }
    function tick(now) {
      const t = Math.min(1, (now - start) / duration);
      const eased = 1 - Math.pow(1 - t, 3);
      const v = Math.floor(target * eased);
      setFormatted(v);
      if (t < 1) requestAnimationFrame(tick);
      else setFormatted(target);
    }
    requestAnimationFrame(tick);
  }

  // -------- Intersection reveal + counters --------
  const io = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.classList.add('on');
        if (e.target.dataset.counter) {
          animateCounter(e.target, parseInt(e.target.dataset.counter, 10));
        }
        io.unobserve(e.target);
      }
    });
  }, { threshold: 0.15 });

  document.querySelectorAll('.reveal, [data-counter]').forEach(el => io.observe(el));

  // Ensure anything already in the viewport on load reveals immediately
  requestAnimationFrame(() => {
    const vh = window.innerHeight;
    document.querySelectorAll('.reveal, [data-counter]').forEach(el => {
      const r = el.getBoundingClientRect();
      if (r.top < vh && r.bottom > 0) {
        el.classList.add('on');
        if (el.dataset.counter) {
          animateCounter(el, parseInt(el.dataset.counter, 10));
        }
        io.unobserve(el);
      }
    });
  });

  // -------- Nav scrolled state --------
  const nav = document.getElementById('nav');
  window.addEventListener('scroll', () => {
    /* 60 px threshold per spec — enough to clear the hero eyebrow */
    nav.classList.toggle('scrolled', window.scrollY > 60);
  });

  // -------- Language toggle button in nav --------
  const langToggle = document.getElementById('langToggle');
  if (langToggle) {
    langToggle.addEventListener('click', () => {
      const next = CURRENT_LOCALE === 'en' ? 'ar' : 'en';
      const target = next === 'ar' ? langToggle.dataset.arUrl : langToggle.dataset.enUrl;
      if (target) window.location.href = target;
    });
  }

  // -------- Tweaks plumbing --------
  const tweaksRoot = document.getElementById('tweaks-root');
  function updateTweakUI() {
    document.querySelectorAll('.tweak-row .opts').forEach(opts => {
      const key = opts.dataset.tweak;
      opts.querySelectorAll('button').forEach(b => {
        b.classList.toggle('active', b.dataset.val === TWEAKS[key]);
      });
    });
  }

  document.querySelectorAll('.tweak-row .opts button').forEach(btn => {
    btn.addEventListener('click', () => {
      const key = btn.parentElement.dataset.tweak;
      const val = btn.dataset.val;
      TWEAKS[key] = val;
      if (key === 'lang') {
        const url = val === 'ar' ? langToggle?.dataset.arUrl : langToggle?.dataset.enUrl;
        if (url) { window.location.href = url; return; }
      }
      if (key === 'accent') applyAccent(val);
      if (key === 'density') applyDensity(val);
      updateTweakUI();
      persist({ [key]: val });
    });
  });

  function persist(edits) {
    try {
      window.parent.postMessage({ type: '__edit_mode_set_keys', edits }, '*');
    } catch (e) {}
  }

  // Tweaks host protocol
  window.addEventListener('message', (e) => {
    const d = e.data || {};
    if (d.type === '__activate_edit_mode') tweaksRoot.classList.add('on');
    if (d.type === '__deactivate_edit_mode') tweaksRoot.classList.remove('on');
  });
  window.parent.postMessage({ type: '__edit_mode_available' }, '*');

  // -------- Ledger date --------
  function updateLedgerDate() {
    const el = document.getElementById('ledgerDate');
    if (!el) return;
    const locale = CURRENT_LOCALE === 'ar' ? 'ar-SA-u-ca-gregory' : 'en-GB';
    const formatted = new Intl.DateTimeFormat(locale, {
      day: '2-digit',
      month: 'short',
      year: 'numeric',
    }).format(new Date());
    el.textContent = CURRENT_LOCALE === 'ar' ? formatted : formatted.toUpperCase();
  }

  // -------- Init --------
  renderFeed();
  setInterval(refreshFeedFromServer, FEED_POLL_MS);
  document.addEventListener('visibilitychange', () => {
    if (!document.hidden) refreshFeedFromServer();
  });
  updateLedgerDate();
  applyAccent(TWEAKS.accent);
  applyDensity(TWEAKS.density);
  updateTweakUI();
</script>

{{-- Quick Donation Floating Widget --}}

{{-- Floating button --}}
<button class="qd-fab" id="qdFab" aria-label="{{ __('Quick Donation') }}">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
  {{ __('Quick Donation') }}
</button>

{{-- Overlay --}}
<div class="qd-overlay" id="qdOverlay"></div>

{{-- Panel --}}
<div class="qd-panel" id="qdPanel">
  <div class="qd-header">
    <h3>{{ __('Quick Donation') }}</h3>
    <button class="qd-close" id="qdClose" aria-label="{{ __('Close') }}">&times;</button>
  </div>
  <div class="qd-body">
    <form method="POST" action="{{ route('guest.donation.initiate') }}" id="qdForm">
      @csrf

      {{-- Preset amounts --}}
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

      {{-- Custom amount --}}
      <div class="qd-amount-wrap">
        <label for="qdAmountInput">{{ __('Donation Amount') }}</label>
        <svg class="qd-amount-prefix" viewBox="0 0 1124.14 1256.39"><path d="M699.62,1113.02h0c-20.06,44.48-33.32,92.75-38.4,143.37l424.51-90.24c20.06-44.47,33.31-92.75,38.4-143.37l-424.51,90.24Z"/><path d="M1085.73,895.8c20.06-44.47,33.32-92.75,38.4-143.37l-330.68,70.33v-135.2l292.27-62.11c20.06-44.47,33.32-92.75,38.4-143.37l-330.68,70.27V66.13c-50.67,28.45-95.67,66.32-132.25,110.99v403.35l-132.25,28.11V0c-50.67,28.44-95.67,66.32-132.25,110.99v525.69l-295.91,62.88c-20.06,44.47-33.33,92.75-38.42,143.37l334.33-71.05v170.26l-358.3,76.14c-20.06,44.47-33.32,92.75-38.4,143.37l375.04-79.7c30.53-6.35,56.77-24.4,73.83-49.24l68.78-101.97v-.02c7.14-10.55,11.3-23.27,11.3-36.97v-149.98l132.25-28.11v270.4l424.53-90.28Z"/></svg>
        <input type="number" name="amount" id="qdAmountInput" class="qd-amount-input"
               placeholder="{{ __('Donation Amount') }}" step="0.01" min="1" max="999999.99" required>
      </div>

      {{-- Validation error --}}
      <div class="qd-error" id="qdError"></div>

      {{-- Info message --}}
      <div class="qd-info">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
        <span>{{ __('Your donation will automatically go to the most needy cases') }}</span>
      </div>

      {{-- Payment icons --}}
      <div class="qd-payment-icons">
        <img src="{{ asset('images/icons/visa-icon.svg') }}" alt="Visa">
        <img src="{{ asset('images/icons/apple-icon.svg') }}" alt="Apple Pay">
        <img src="{{ asset('images/icons/mastercard-icon.svg') }}" alt="Mastercard">
        <img src="{{ asset('images/icons/mada-icon.svg') }}" alt="Mada">
      </div>

      {{-- Submit --}}
      <button type="submit" class="qd-submit" id="qdSubmit">{{ __('Donate Now') }}</button>
    </form>
  </div>
</div>

<script>
(function() {
  const fab     = document.getElementById('qdFab');
  const overlay = document.getElementById('qdOverlay');
  const panel   = document.getElementById('qdPanel');
  const closeBtn= document.getElementById('qdClose');
  const form    = document.getElementById('qdForm');
  const input   = document.getElementById('qdAmountInput');
  const errorEl = document.getElementById('qdError');
  const presets = panel.querySelectorAll('.qd-preset');
  const submitBtn = document.getElementById('qdSubmit');

  function open() {
    fab.classList.add('hidden');
    overlay.classList.add('on');
    // Force reflow so the transition plays
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

  // Preset buttons
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

  // Client-side validation
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
})();
</script>
</body>
</html>
