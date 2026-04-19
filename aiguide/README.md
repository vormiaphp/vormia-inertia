# Vormia Inertia — AI guides

This folder contains `.mdc` guides for implementing **Vormia + Laravel + Inertia** with **Vue 3**, **React**, or **Svelte**. Each file is scoped with `globs` so assistants prefer the right adapter when you edit matching files.

## Available guides

| File | When to use |
|------|----------------|
| [`vormia-inertia-react.mdc`](vormia-inertia-react.mdc) | React/TSX pages, `app.tsx` / `app.jsx`, React+Vite Inertia setup |
| [`vormia-inertia-vue.mdc`](vormia-inertia-vue.mdc) | Vue SFC pages, `app.js` / `app.ts` with `@inertiajs/vue3` |
| [`vormia-inertia-svelte.mdc`](vormia-inertia-svelte.mdc) | Svelte 5 pages, `app.js` with `@inertiajs/svelte` |

## Guide structure

Each guide follows the same sections:

1. **Tech stack** — Laravel, Inertia server, client adapter, Vite.
2. **File structure** — where pages, layouts, and bootstrap live.
3. **Pages and layouts** — default export, persistent layouts, naming.
4. **Props and shared data** — `usePage` / equivalent; Vormia `notification` and config hints.
5. **Navigation** — `<Link>`, `router.visit`, preserving scroll and history.
6. **Forms** — `useForm` / adapter patterns; validation errors from Laravel.
7. **MediaForge** — resolve URLs in PHP; never guess disk URLs only in JS.
8. **Authorization** — policies and middleware stay on Laravel; gate props if needed.
9. **Checklist** — bootstrap order for a new screen.
10. **Official resources** — Inertia + framework documentation links.

## Related

- [Vormia (core) README](https://github.com/vormiaphp/vormia/blob/main/README.md) — MediaForge, notifications, API middleware, user model traits.
- [Inertia.js](https://inertiajs.com/) — authoritative client/server behavior.
