---
status: accepted
---

# Laravel + Inertia + React on a self-hosted VPS, MariaDB, passwordless email auth

We needed a full stack for a solo-built, friends-scale app. We chose **Laravel (PHP) + React via Inertia.js**, wired as a single deployable with no separate API layer — no CORS, no token auth, session-based like classic Laravel. Considered a BaaS (Supabase) and a decoupled API+SPA; rejected BaaS because the app's scoring logic (Borda-count voting, tie-breaks) wants to live in server code the app controls rather than client-side queries against row-level policies, and rejected the decoupled API because there's no second client (native app, public API) to justify the extra layer today — it can be added later without a rearchitecture.

**Database**: MariaDB. **Hosting**: self-managed AlmaLinux 9 VPS (VentraIP) at `bookclub.dzal.au`, nginx + PHP-FPM, Let's Encrypt for TLS, manual SSH deploy (`git pull && composer install && npm ci && npm run build && migrate`) — no CI/CD yet, deliberately deferred until there's enough deploy frequency to justify automating it.

**Auth**: passwordless email magic-link, sent via Resend rather than the VPS's own MTA or the domain's existing cPanel mailbox — a fresh VPS has no sending reputation (no PTR, no SPF/DKIM), and a transactional provider sidesteps that without relying on shared cPanel hosting's sending reputation and throughput limits.

**Deferred, not rejected**: real-time updates (Laravel Reverb/WebSockets) and a queue worker (Redis) — v1 ships with refresh-on-load UI and synchronous mail sending; both can be added later without changing the architecture above. Web push notifications *are* in scope for v1 (VAPID + service worker), layered on top of the PWA established in [ADR-0001](./0001-pwa-over-native.md).
