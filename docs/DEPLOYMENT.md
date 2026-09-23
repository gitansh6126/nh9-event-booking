# NH9 Events Platform — Deploy & Test

This is an independent repo of the NH9 Events ticketing platform (Hi.Events-based)
with customizations: WhatsApp ticket delivery, quantity dropdown selector, and
sequential-tier checkout updates. It is deployed to Render purely for testing.

## Deploy to Render (testing)

1. Make sure this repo is on GitHub (`gitansh6126/nh9-event-booking`, branch `main`).
2. Open the Render Blueprint deploy URL for this repo:

   `https://render.com/deploy?repo=https://github.com/gitansh6126/nh9-event-booking`

   Render reads `render.yaml` at the repo root. It creates:
   - a web service `nh9-event-platform` (free plan, Docker) that builds the
     `render/Dockerfile` from this source,
   - a free Postgres database `nh9-platform-db`.

3. Wait for the build (~10–15 min first time) and the migration step
   (`php artisan migrate --force`) that runs on boot.
4. Open the `https://nh9-event-platform.onrender.com` URL. First boot is slow
   because Render free instances spin down after inactivity; give it a moment.

The web service is derived from the [Hi.Events Render blueprint](https://github.com/HiEventsDev/hi.events-render.com).

### Environment notes

- `MAIL_MAILER=array` — no emails are delivered (offline/free bookings only).
- `QUEUE_CONNECTION=sync` — no Redis; queue runs inline.
- `APP_KEY` / `JWT_SECRET` are set by the blueprint.
- Payments are off for testing (`VITE_STRIPE_PUBLISHABLE_KEY` is a placeholder).

## Testing

- **GitHub Actions** runs automatically on push to `main`:
  - `.github/workflows/tests.yml` — backend unit + feature suites (PHPUnit, Postgres).
  - `.github/workflows/e2e.yml` — Playwright E2E (full suite on `main`, `@smoke` lane on PRs).
- **Local frontend validation**:

  ```bash
  cd frontend
  yarn install
  npx tsc --noEmit
  yarn build
  ```

- **Local full-stack / E2E** (requires Docker) — see `e2e/README.md`:
  `./e2e/run-e2e.sh` (hermetic stack on `http://localhost:8123`).

## NH9 customizations (vs upstream Hi.Events)

Current delta:
  - WhatsApp ticket message modal + `buildTicketWhatsAppMessage`/share-link util
    (`frontend/src/components/modals/WhatsAppTicketMessageModal`,
     `frontend/src/utilites/whatsappTicketMessage.ts`).
  - Quantity dropdown selector replacing the stepper
    (`frontend/src/components/routes/product-widget/SelectProducts/Prices/QuantitySelect`).
  - E2E updates for the quantity selector (`e2e/pages/checkout.page.ts`,
     `e2e/tests/checkout/sequential-tier-checkout.spec.ts`).