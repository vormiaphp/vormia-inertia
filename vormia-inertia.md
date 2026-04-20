# Vormia Inertia Developer Guide

This document explains what the package does, what it does not do, which commands are available, and which helpers developers should call when building an Inertia-based Vormia application.

## Package Scope

`vormiaphp/vormia-inertia` is the Laravel-side bridge between:

- core Vormia on the backend
- Inertia on the response layer
- React, Vue, or Svelte on the page layer

It is not a full starter kit and it does not replace the core Vormia package.

## What The Package Ships

### Commands

- `php artisan vormia-inertia:install`
- `php artisan vormia-inertia:uninstall`

### Published and generated files

The installer can create or patch:

- `config/vormia-inertia.php`
- `app/Http/Middleware/HandleInertiaRequests.php`
- `bootstrap/app.php`
- `resources/views/app.blade.php`
- `resources/js/app.*`
- `resources/css/app.css`

### Package helper access

```php
use VormiaPHP\VormiaInertia\Facades\VormiaInertia;

VormiaInertia::name();
app('vormia.inertia')->name();
```

Right now the package helper surface is intentionally small. The main value of the package is the host-app bridge and command tooling.

## What Still Belongs To Core Vormia

Use `vormiaphp/vormia` for application behavior and business features:

- `NotificationService::current()`
- `MediaForge::url($path)->public()`
- `MediaForge::url($path)->private()`
- upload handling, roles, permissions, notifications, and MediaForge storage rules

Example:

```php
use Inertia\Inertia;
use Vormia\Vormia\Services\NotificationService;
use VormiaPHP\Vormia\Facades\MediaForge;

return Inertia::render('Profile/Show', [
    'notification' => NotificationService::current(),
    'avatar' => MediaForge::url($avatarPath)->public(),
    'preview' => MediaForge::url($avatarPath)->private(),
]);
```

The rule of thumb is simple: resolve media URLs and notification data in PHP, then pass clean props into Inertia responses.

## Install Command

Basic interactive usage:

```sh
php artisan vormia-inertia:install
```

Scripted usage:

```sh
php artisan vormia-inertia:install --stack=vue --lang=ts
```

Supported options:

- `--stack=react|vue|svelte`
- `--lang=js|ts`
- `--replace=app.js`
- `--replace=app.css`
- `--replace=app.tsx`
- `--replace=app.jsx`
- `--replace=app.ts`
- `--force`

### Replace flag behavior

`--replace=app.js` is the stable user-facing alias. It resolves to the selected stack’s real entry filename:

- React + TS: `resources/js/app.tsx`
- React + JS: `resources/js/app.jsx`
- Vue + TS: `resources/js/app.ts`
- Vue + JS: `resources/js/app.js`
- Svelte + TS: `resources/js/app.ts`
- Svelte + JS: `resources/js/app.js`

`--replace=app.css` targets `resources/css/app.css`.

If you do not pass a replace flag, the installer will not overwrite existing entry assets in non-interactive mode. In interactive mode it can ask first.

### What install patches automatically

- Adds `HandleInertiaRequests` shared props or creates the middleware if it does not exist
- Appends `HandleInertiaRequests::class` to the web middleware stack in `bootstrap/app.php`
- Creates an Inertia root view if one is missing
- Creates minimal stack-aware `resources/js/app.*`
- Creates minimal `resources/css/app.css` if missing

### What install does not patch automatically

- `vite.config.js` or `vite.config.ts`
- page components under `resources/js/Pages`
- Tailwind, UI libraries, or a design system

You still need to wire the correct Vite plugin for React, Vue, or Svelte in the host app.

## Uninstall Command

```sh
php artisan vormia-inertia:uninstall
```

Force mode:

```sh
php artisan vormia-inertia:uninstall --force
```

The uninstall flow is conservative:

- marker-managed changes in `bootstrap/app.php` are removed
- shared props inserted by the installer are removed
- full generated files are only deleted when they still match the original stub
- modified generated files are left in place

That safety behavior matters when a team has started customizing the generated bridge files.

## Stack Matrix

| Stack | JS entry | TS entry | Main package | Typical component files |
| --- | --- | --- | --- | --- |
| React | `resources/js/app.jsx` | `resources/js/app.tsx` | `@inertiajs/react` | `.jsx`, `.tsx` |
| Vue | `resources/js/app.js` | `resources/js/app.ts` | `@inertiajs/vue3` | `.vue` |
| Svelte | `resources/js/app.js` | `resources/js/app.ts` | `@inertiajs/svelte` | `.svelte` |

## Client-Side Helpers Developers Call

After installation, the main adapter helpers to use in application code are:

- `usePage`
- `useForm`
- `Link`
- `router.visit`

Those are the helpers to reach for in pages, forms, and client-side navigation.

## Shared Props Contract

The generated middleware shares:

- `notification`
- `auth.user`
- `vormia.inertia.package`

That gives you a predictable place to read notification and user data on every navigation.

## Reference Example Versus Generated Stubs

This repository includes a richer `inertia/` reference app. Treat it as a maintainers’ example, not as the exact file set produced by `php artisan vormia-inertia:install`.

The generated stubs are deliberately smaller so they can fit into an existing Laravel application with less risk.

## Recommended Workflow

1. Install core Vormia.
2. Require `vormiaphp/vormia-inertia`.
3. Run `php artisan vormia-inertia:install`.
4. Update `vite.config.js` or `vite.config.ts` for the chosen stack.
5. Add your page components.
6. Use `NotificationService::current()`, `MediaForge::url($path)->public()`, and `MediaForge::url($path)->private()` in PHP before passing props into Inertia.
7. Use `usePage`, `useForm`, `Link`, and `router.visit` in the client app.
