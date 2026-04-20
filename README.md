# Vormia Inertia

[![Packagist](https://img.shields.io/packagist/v/vormiaphp/vormia-inertia.svg)](https://packagist.org/packages/vormiaphp/vormia-inertia)
[![GitHub](https://img.shields.io/github/stars/vormiaphp/vormia-inertia.svg)](https://github.com/vormiaphp/vormia-inertia)

`vormiaphp/vormia-inertia` is the Inertia bridge for Vormia-powered Laravel apps. It adds the Laravel Inertia adapter as a package dependency, ships install and uninstall commands for host apps, and provides a small service accessor for package-level helpers.

## What This Package Does

- Registers `php artisan vormia-inertia:install`
- Registers `php artisan vormia-inertia:uninstall`
- Publishes `config/vormia-inertia.php`
- Scaffolds a minimal Inertia host-app bridge:
  - `app/Http/Middleware/HandleInertiaRequests.php`
  - `bootstrap/app.php` middleware append block
  - `resources/views/app.blade.php`
  - stack-aware `resources/js/app.*`
  - `resources/css/app.css`
- Adds a container binding and facade for `app('vormia.inertia')` and `VormiaInertia::name()`

## What Still Comes From Core Vormia

This package does not replace the core Vormia package. These still come from `vormiaphp/vormia`:

- `NotificationService::current()`
- `MediaForge::url($path)->public()`
- `MediaForge::url($path)->private()`
- roles, permissions, tokens, MediaForge uploads, and the rest of the Vormia backend

## Installation

Require the package in a Laravel app that already has `vormiaphp/vormia` installed:

```sh
composer require vormiaphp/vormia-inertia
```

You can still publish the config manually:

```sh
php artisan vendor:publish --tag=vormia-inertia-config
```

## Commands

### Install

Interactive install:

```sh
php artisan vormia-inertia:install
```

Non-interactive install:

```sh
php artisan vormia-inertia:install --stack=react --lang=ts
```

Replace entry assets explicitly:

```sh
php artisan vormia-inertia:install --stack=react --lang=ts --replace=app.js --replace=app.css
```

Supported flags:

- `--stack=react|vue|svelte`
- `--lang=js|ts`
- `--replace=app.js`
- `--replace=app.css`
- `--replace=app.tsx`
- `--replace=app.jsx`
- `--replace=app.ts`
- `--force`

`--replace=app.js` is the user-facing alias for the selected stack’s real entry file. Examples:

- React + TypeScript maps `--replace=app.js` to `resources/js/app.tsx`
- React + JavaScript maps `--replace=app.js` to `resources/js/app.jsx`
- Vue + TypeScript maps `--replace=app.js` to `resources/js/app.ts`
- Vue + JavaScript maps `--replace=app.js` to `resources/js/app.js`
- Svelte + TypeScript maps `--replace=app.js` to `resources/js/app.ts`
- Svelte + JavaScript maps `--replace=app.js` to `resources/js/app.js`

### Uninstall

```sh
php artisan vormia-inertia:uninstall
```

Force mode:

```sh
php artisan vormia-inertia:uninstall --force
```

The uninstall command removes marker-managed changes from `bootstrap/app.php` and shared props, and only deletes full stub files when they are still unchanged from the generated package stub.

## Stack Matrix

| Stack | JS entry | TS entry | Main adapter |
| --- | --- | --- | --- |
| React | `resources/js/app.jsx` | `resources/js/app.tsx` | `@inertiajs/react` |
| Vue | `resources/js/app.js` | `resources/js/app.ts` | `@inertiajs/vue3` |
| Svelte | `resources/js/app.js` | `resources/js/app.ts` | `@inertiajs/svelte` |

## Helpers Developers Will Use

### Package helper

```php
use VormiaPHP\VormiaInertia\Facades\VormiaInertia;

VormiaInertia::name();
app('vormia.inertia')->name();
```

### Core Vormia helpers in Inertia apps

Use the core package on the PHP side and pass resolved data into page props:

```php
use Inertia\Inertia;
use Vormia\Vormia\Services\NotificationService;
use VormiaPHP\Vormia\Facades\MediaForge;

return Inertia::render('Dashboard', [
    'notification' => NotificationService::current(),
    'avatarUrl' => MediaForge::url($path)->public(),
    'privatePreviewUrl' => MediaForge::url($path)->private(),
]);
```

### Inertia adapter helpers

On the client side, you will usually work with the adapter helpers for your chosen stack:

- `usePage`
- `useForm`
- `Link`
- `router.visit`

These are the helpers developers reach for most often after the package install completes.

## Shared Props

The generated `HandleInertiaRequests.php` shares:

- `notification` from `NotificationService::current()`
- `auth.user` as a minimal JSON-safe user subset
- `vormia.inertia.package` for a tiny bit of package metadata

## Important Notes

- The installer patches Laravel-side bridge files automatically, but it does not rewrite your Vite config for you.
- You still need to add the correct framework plugin to `vite.config.js` or `vite.config.ts`.
- The generated front-end files are intentionally minimal. They are a bridge, not a full starter kit.
- The `inertia/` folder in this repository is a richer reference app, not the exact output of the installer.

## Reference Material

- Full developer guide: [vormia-inertia.md](vormia-inertia.md)
- AI/editor guides index: [aiguide/README.md](aiguide/README.md)
- React guide: [aiguide/vormia-inertia-react.mdc](aiguide/vormia-inertia-react.mdc)
- Vue guide: [aiguide/vormia-inertia-vue.mdc](aiguide/vormia-inertia-vue.mdc)
- Svelte guide: [aiguide/vormia-inertia-svelte.mdc](aiguide/vormia-inertia-svelte.mdc)

## Testing

```sh
composer test
```

## License

MIT
