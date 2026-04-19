# Vormia Inertia — Laravel Inertia.js bridge for Vormia

[![Packagist](https://img.shields.io/packagist/v/vormiaphp/vormia-inertia.svg)](https://packagist.org/packages/vormiaphp/vormia-inertia)
[![GitHub](https://img.shields.io/github/stars/vormiaphp/vormia-inertia.svg)](https://github.com/vormiaphp/vormia-inertia)

## AI guides

The [`aiguide/`](aiguide/) folder holds `.mdc` guides for building **Vormia + Inertia** front ends with **Vue 3**, **React**, or **Svelte**. Point your editor or agent at them when scaffolding pages, Vite config, and shared props.

| Guide | Stack |
|-------|--------|
| [vormia-inertia-react.mdc](aiguide/vormia-inertia-react.mdc) | Inertia + React (`@inertiajs/react`) |
| [vormia-inertia-vue.mdc](aiguide/vormia-inertia-vue.mdc) | Inertia + Vue 3 (`@inertiajs/vue3`) |
| [vormia-inertia-svelte.mdc](aiguide/vormia-inertia-svelte.mdc) | Inertia + Svelte 5 (`@inertiajs/svelte`) |

Index and conventions: [aiguide/README.md](aiguide/README.md).

**Core Vormia package:** [vormiaphp/vormia](https://packagist.org/packages/vormiaphp/vormia) — installation, MediaForge, notifications, and roles live there. This package adds the Inertia Laravel adapter as a first-class dependency and a place for Inertia-specific configuration and tooling.

---

## Introduction

**Vormia Inertia** connects [Vormia](https://github.com/vormiaphp/vormia) to [Inertia.js](https://inertiajs.com/): Laravel still owns routing, authorization, and responses, while Vue, React, or Svelte render pages without you maintaining a separate JSON API for every screen.

Typical responsibilities:

- **Laravel + Vormia**: models, `MediaForge`, `NotificationService`, permissions, and `Inertia::render()` with page props.
- **Inertia adapter**: page components, layouts, `<Link>`, `router.visit`, and form helpers.
- **This package**: Composer wiring to `inertiajs/inertia-laravel`, publishable config, and the `vormia.inertia` service accessor for future helpers.

A **reference demo** (React + TypeScript + Tailwind 4 + Vite) lives under [`inertia/`](inertia/) in this repository — useful when aligning your app’s `vite.config`, `tsconfig`, and `resources/js` layout with the maintainers’ defaults.

## Dependencies

### Required (declared in Composer)

- **PHP** `^8.2`
- **Laravel** `^12.0|^13.0`
- **[vormiaphp/vormia](https://packagist.org/packages/vormiaphp/vormia)** `^5.4` — media, notifications, RBAC, and related services.
- **[inertiajs/inertia-laravel](https://packagist.org/packages/inertiajs/inertia-laravel)** `^2.0` — server-side Inertia middleware and responses.

Install the **client** adapter and Vite plugin in your app’s `package.json` depending on the stack you choose (React, Vue, or Svelte). Version lines should match what your Laravel and Inertia server packages expect; see the [Inertia documentation](https://inertiajs.com/) for the current matrix.

### Suggested

- **[laravel/wayfinder](https://github.com/laravel/wayfinder)** — typed route/action helpers from Laravel routes (optional; the demo tree may include generated Wayfinder stubs for illustration).

## Features

- **Composer integration** — auto-discovery registers `VormiaPHP\VormiaInertia\VormiaInertiaServiceProvider` and the `VormiaInertia` facade alias.
- **Configuration** — merge/publish `config/vormia-inertia.php` for package-specific options as the bridge grows.
- **Service accessor** — `app('vormia.inertia')` resolves `VormiaPHP\VormiaInertia\VormiaInertia` for future helpers without coupling core Vormia to front-end stacks.

## Installation

Prerequisites: a Laravel application with **Vormia** already required and configured the way you want (see the [Vormia README](https://github.com/vormiaphp/vormia/blob/main/README.md) for `composer require vormiaphp/vormia`, `php artisan vormia:install`, migrations, and user model setup).

### 1. Require this package

```sh
composer require vormiaphp/vormia-inertia
```

### 2. Publish configuration (optional)

```sh
php artisan vendor:publish --tag=vormia-inertia-config
```

Edit `config/vormia-inertia.php` after publishing.

### 3. Finish Inertia in the host app

Follow the official **Inertia + Laravel** guide for your chosen client:

- [Server-side setup](https://inertiajs.com/server-side-setup) — root template, middleware, and `HandleInertiaRequests`.
- [Client-side setup](https://inertiajs.com/client-side-setup) — Vite entry, `createInertiaApp`, and the Vue / React / Svelte adapter.

Use the [`aiguide/`](aiguide/) files in this repo as a **Vormia-flavored** companion (shared props, MediaForge URLs, notifications).

## Usage

### Configuration

The merged config key is `vormia-inertia`. After publishing, change values there; the default file is intentionally minimal so upgrades stay safe.

### Facade and container

```php
use VormiaPHP\VormiaInertia\Facades\VormiaInertia;

VormiaInertia::name(); // "Vormia Inertia"

// Equivalent:
app('vormia.inertia')->name();
```

### MediaForge and Inertia

`MediaForge::upload(...)->run()` returns a **storage path or key**, not always a public URL. Resolve URLs in PHP and pass **strings** (or DTOs) to `Inertia::render()` so the SPA only deals with ready-to-use `src` values. See the Vormia README sections on `MediaForge::url()`, signed URLs, and optional preview proxy mode.

### Notifications

Flash or session notifications from Vormia should be exposed to the client through **`HandleInertiaRequests::share()`** (or a dedicated middleware), e.g. mapping `NotificationService::current()` (or your app’s equivalent) to a `notification` prop. Consume that prop once per navigation in a small bridge component (toast, Sonner, SweetAlert2, etc.).

## Demo folder (`inertia/`)

The `inertia/` directory in the **source repository** is a reference front end (routes, controllers, `resources/js`, Vite, Tailwind 4). It is not installed into consumer apps automatically; copy or compare patterns when bootstrapping your own project.

## Testing (package development)

```sh
composer install
composer test
```

## License

MIT.
