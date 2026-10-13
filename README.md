# Fantasy Draft Lottery

Fantasy Draft Lottery is an invitation-only application for running a private fantasy keeper-league draft lottery. League members configure an initial team order and integer basis-point odds, then either run one authoritative official draw or repeat transient simulations.

The MVP implements the `weighted_single_pick` format. One weighted team receives draft position #1. Every other team retains its original relative order and shifts around the winner, producing a complete final order for every draw.

## MVP behaviour

- Public registration is disabled. Platform administrators invite users by email.
- Each lottery belongs to one user; administrators do not automatically gain access to private lotteries.
- Draft lotteries can be incomplete. Ready lotteries require at least two teams, positive weights, contiguous display order, and exactly 10,000 basis points (100.00%).
- An official lottery runs once, saves one result row per team, and becomes immutable. Replay reads its saved snapshots and never redraws.
- A simulation can run repeatedly and never creates official draw or result records.
- Simulation win counts exist only in browser memory for the current page session. Refreshing or navigating away resets them, and they never change configured odds.
- Archived lotteries are read-only. A completed official order remains visible after archival.

## Stack and requirements

- PHP 8.4.1 or newer within PHP 8.x, and Composer 2
- PHP extensions required by Laravel and the locked dependencies, including PDO SQLite for the default database; verify with `composer check-platform-reqs`
- Laravel 13
- Livewire 4 and Alpine.js supplied by Livewire
- Flux UI 2 and Tailwind CSS 4
- Vite 8 and Node.js 22.12+ with npm; Node.js 22 or 24 LTS is recommended (Vite also supports Node.js 20.19+)
- Pest 4, Pint, Larastan level 7, and Laravel Boost
- Laravel Herd is recommended for local development on Windows, but is optional.

## Local setup with Herd

From the project directory:

```powershell
composer install
npm ci
Copy-Item .env.example .env
php artisan key:generate
```

The default configuration uses SQLite. Create `database/database.sqlite` if Herd or the installer has not already created it, then migrate:

```powershell
New-Item database/database.sqlite -ItemType File -ErrorAction SilentlyContinue
php artisan migrate
```

Set the three `PLATFORM_ADMIN_*` values described below, then create the first administrator:

```powershell
php artisan db:seed --class=PlatformAdministratorSeeder
```

Set `APP_URL` to the actual Herd origin (including its HTTP or HTTPS scheme), then start frontend development assets with `npm run dev` and open that origin. The usual site hostname is `fantasy-league-draft-lottery.test`. The origin must match for passkeys to work.

Without Herd, run `php artisan serve` alongside `npm run dev` and set `APP_URL=http://localhost:8000`. For built assets, use `npm run build` instead of the Vite development server. Herd is optional.

The Vite font plugin downloads Instrument Sans from Bunny during builds, so the build environment needs outbound access. An optional `fontaine` warning concerns optimized fallback fonts and does not prevent the build.

Portable Unix equivalents use `cp .env.example .env` and `touch database/database.sqlite`.

## Environment configuration

Configure normal Laravel application, database, and mail variables. Important application-specific values are:

```dotenv
INVITATION_EXPIRES_IN_DAYS=7
PLATFORM_ADMIN_NAME=
PLATFORM_ADMIN_EMAIL=
PLATFORM_ADMIN_PASSWORD=
```

The administrator seeder normalizes the email. Missing values cause a clear warning and safely skip creation. A new matching user receives the configured password through Laravel's hashed cast. Re-running the seeder promotes/reactivates an existing matching user and updates the name, but deliberately does **not** overwrite that user's password.

For invitation email delivery, configure `MAIL_MAILER`, host, port, credentials, and a valid `MAIL_FROM_ADDRESS`. The local default writes mail to the log.

Log mail contains invitation acceptance URLs and recipient addresses. Treat logs as private. Local `.env`, Composer `auth.json`, databases, uploads, generated assets, and runtime files must remain outside Git. `.env.example` contains placeholders only.

For production:

- use `APP_ENV=production` and `APP_DEBUG=false`;
- set `APP_URL` to the public HTTPS origin;
- require HTTPS and set `SESSION_SECURE_COOKIE=true`;
- use a production database and production mail provider;
- store secrets outside version control;
- set `QUEUE_CONNECTION=sync` unless queued work is deliberately introduced later.

## Administration workflow

After signing in as the seeded administrator:

1. Open **Administration → Invitations**.
2. Send an invitation to a normalized email address.
3. The recipient follows the one-time emailed URL, chooses a password, and receives an already-verified account.
4. Expired or revoked invitations can be rotated into a new acceptance link. Raw tokens are never shown in the administration UI or stored in plaintext.
5. Administrators can suspend or reactivate users. Self-suspension and suspension of the final active administrator are blocked.

Suspended users are logged out and denied normal routes and crafted Livewire requests. Administrators manage platform access only; they cannot inspect or operate another user's lotteries.

## Lottery workflow

1. Create a lottery and choose **Official** or **Simulation** mode.
2. Add teams in their initial draft order and assign odds. Save incomplete work as a draft.
3. Mark the configuration ready once the total is exactly 100.00%.
4. Choose the lottery's default official reveal mode: **Manual** or **Autoplay**. This setting applies only to official presentations and replays; it does not slow simulations.
5. For an official lottery, confirm the irreversible draw. The result is generated and saved before the focused presentation opens. The configured mode is preselected, the presentation waits for **Start Lottery**, then reveals picks from N through #1. Replay waits for **Start Replay** and uses the identical saved snapshot.
6. For a simulation, run as often as needed. **Simulate Again** requests a new server result without closing and reopening the result dialog; Alpine never chooses a winner.
7. The simulation page shows temporary session counts, configured odds, wins, and observed rates. **Reset simulation stats** performs no server request.
8. Archive a lottery when it should become read-only.

The completed official page always renders the authoritative final order on the server, independent of animation or JavaScript.

## Development sample data

After seeding an administrator, optionally create realistic local samples:

```powershell
php artisan db:seed --class=DevelopmentSampleSeeder
```

This explicitly invoked seeder is disabled in production. It creates a ready official lottery, a ready simulation, a completed official lottery using the real transactional draw action, and an archived sample. It is idempotent by sample name.

## Tests and quality checks

```powershell
php artisan test
php artisan test tests\Feature\Lotteries
vendor\bin\pint --parallel --test
vendor\bin\phpstan analyse --memory-limit=1G
npm run build
git diff --check
php artisan migrate:status
```

Use forward slashes in test paths on Unix-like systems.

Tests use an in-memory SQLite database, array sessions/cache/mail, and synchronous queues. They do not validate production database concurrency, real email delivery, or browser animation/accessibility. Manual browser checklists are in `docs/`.

## Architecture

- Livewire components handle page delivery, transient form state, and browser events.
- Application actions own invitation, user-administration, lottery mutation, draw, simulation, and replay use cases.
- Policies enforce ownership and keep platform administration separate from private lottery capabilities.
- Validation/domain services construct authoritative database-backed configurations and execute the weighted engine through an injectable secure RNG.
- Eloquent stores lotteries, teams, one official draw, per-position results, and immutable snapshots.
- Alpine controls only presentation timing, explicit start/skip/replay state, and ephemeral simulation counters.

The application/action and DTO boundaries would allow a future Inertia/React delivery layer without moving winner selection into the client. No such frontend is included in the MVP.

## Production notes

SQLite is the default and the automated test database. It has coarse write locking and should not be relied on for competing official-draw requests. MySQL and PostgreSQL require deployment-specific verification before use; PostgreSQL compatibility has not been established. Database uniqueness constraints and transactional checks remain the final integrity backstop.

Deploy over HTTPS, configure real mail delivery, back up the database and application secrets, run migrations as a controlled deployment step, and verify `/up` through infrastructure health checks. Invitation email could move to a queue in a future operational phase, but the MVP sends synchronously.

The application currently requires no queue worker or scheduler. `.env.example` retains Laravel's database queue default; production can use `QUEUE_CONNECTION=sync`. The optional `composer dev` command starts a worker as part of the inherited development tooling. No application scheduled jobs are defined in `routes/console.php`.

Account self-deletion requires the current password but does not enforce the final-administrator safeguard used by suspension. Keep an administrator recovery procedure. Official-draw references can also prevent deletion of an account that has run draws.

## License and third-party notices

No license has been selected for the application's original code.

Third-party dependencies and inherited material remain subject to their respective licenses. See [third-party notices](THIRD_PARTY_NOTICES.md) and the [licenses directory](licenses/) for preserved upstream notices.

The free Flux package is installed separately through Composer; its implementation source is not bundled in this repository.

## Future possibilities—not implemented

- multiple independently weighted draft positions;
- additional lottery formats;
- league or organization ownership;
- public shareable simulations;
- persisted simulation analytics;
- team logos;
- an Inertia/React/TypeScript frontend.

These are explicitly outside the current implementation.
