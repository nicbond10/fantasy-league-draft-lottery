# Publication review

Reviewed October 13, 2026. Nothing was staged, committed, pushed, or published.

The 149 pending file-level changes at the start were reviewed as application source, configuration, migrations/factories/seeders, frontend code/views, automated tests, and manual validation documentation. The list below also includes publication-preparation changes.

## Review decisions

- Keep application source and authorization regression tests, migrations, factories and explicit seeders, frontend source, dependency lockfiles, safe .env.example, CI configuration, directory placeholders, and manual browser validation docs.
- Include the deletion of the obsolete resources/views/welcome.blade.php; the home route now uses resources/views/index.blade.php.
- Remove the unused resources/views/flux/navlist/group.blade.php override; keep the four inherited Lucide icon wrappers and Flux as a Composer dependency. Historical copies of the removed override remain in Git and retain the documented publication-rights question.
- Exclude local secrets, Composer auth.json, database files/dumps, logs, sessions, generated caches/assets/fonts, uploads, vendor, node_modules, backups, and local agent/editor configuration.
- Existing application code has no newly selected license. Removed the inherited application-wide MIT metadata declaration; upstream notices are preserved separately.

## Checks

- Full Pest suite: 152 passed, 469 assertions; in-memory SQLite.
- Full Pint formatting check passed; configured PHPStan analysis passed with zero errors.
- Production Vite build passed using installed Node 24.18.0; optional fontaine fallback-font warning only.
- Composer platform requirements passed on PHP 8.4.26. Validation found no schema or lockfile errors, with an intentional missing-application-license warning.
- Locked dependency names, versions, and source/dist references are unchanged; only lock content-hash and PHP platform constraint changed.
- Secret-pattern scan: 237 current publication text files and 282 reachable historical blobs; no matches. Patterns cover private keys, common provider tokens, credential-bearing URLs, and populated environment secret assignments.
- No sensitive/runtime/dependency files are tracked; ignored-but-tracked check returned zero. .env.example secret fields are blank/null and its email/host values are placeholders.
- Existing history contains four starter/tooling commits. Prior review found personal Gmail commit identity and no configured remote; public Git history exposes that identity.

## Limits and unresolved questions

- Secret scanning is heuristic, not a guarantee; binary assets are inherited starter-kit icons/favicons, not application uploads. Local ignored database/log data is private and excluded, not approved for publication.
- Navigation browser checks passed in headless Edge at desktop 1440x900 and touch-emulated mobile 390x844: sidebar links, settings links, password-confirmation flow, mobile initial collapse/open/close/reopen and navigation auto-collapse, with no JavaScript errors or horizontal overflow. An isolated disposable test database was used. Desktop sidebar collapse is not configured. Narrow desktop mouse emulation exposed a sidebar reopen pointer-interception timeout both with the historical override restored and removed; this pre-existing behavior requires separate investigation. No full browser acceptance run, real mail-provider test, clean-machine installation, or production database/concurrency test was performed.
- The current-source copy of the unused navlist group is removed without replacing it or copying vendor source. Git history still contains it. The historical redistribution question and uncertainty about the free dependency's broad terms are documented in THIRD_PARTY_NOTICES.md; no permission is inferred.
- Known account-deletion and non-SQLite deployment limitations are documented in README.md.

## Commit candidate files

These paths are candidates for the future application commit, subject to resolving the publication-rights question. This is a review list, not a staging command. Required unchanged tracked source/configuration/lockfiles/placeholders remain in the repository.

```text
add     app/Actions/Invitations/AcceptInvitation.php
add     app/Actions/Invitations/InviteUser.php
add     app/Actions/Invitations/RevokeInvitation.php
add     app/Actions/Lotteries/ArchiveLottery.php
add     app/Actions/Lotteries/CreateLottery.php
add     app/Actions/Lotteries/GetLotteryReplay.php
add     app/Actions/Lotteries/RunLotterySimulation.php
add     app/Actions/Lotteries/RunOfficialLotteryDraw.php
add     app/Actions/Lotteries/UpdateLottery.php
add     app/Actions/Users/ReactivateUser.php
add     app/Actions/Users/SuspendUser.php
add     app/Contracts/RandomNumberGenerator.php
add     app/Data/DraftOrderResultData.php
add     app/Data/DraftPositionData.php
add     app/Data/LotteryData.php
add     app/Data/LotteryTeamData.php
add     app/Enums/LotteryFormat.php
add     app/Enums/LotteryMode.php
add     app/Enums/LotteryPresentationMode.php
add     app/Enums/LotteryStatus.php
add     app/Enums/UserStatus.php
add     app/Exceptions/LotteryCannotBeRun.php
add     app/Exceptions/OfficialDrawAlreadyCompleted.php
add     app/Http/Middleware/EnsurePlatformAdministrator.php
add     app/Http/Middleware/EnsureUserIsActive.php
add     app/Http/Middleware/RequirePasswordConfirmation.php
add     app/Livewire/Admin/Dashboard.php
add     app/Livewire/Admin/Invitations/Index.php
add     app/Livewire/Admin/Users/Index.php
add     app/Livewire/Auth/AcceptInvitation.php
add     app/Livewire/Dashboard.php
add     app/Livewire/Lotteries/CreateLottery.php
add     app/Livewire/Lotteries/EditLottery.php
add     app/Livewire/Lotteries/Index.php
add     app/Livewire/Lotteries/LotteryForm.php
add     app/Livewire/Lotteries/ShowLottery.php
add     app/Mail/UserInvitationMail.php
add     app/Models/Lottery.php
add     app/Models/LotteryDraw.php
add     app/Models/LotteryDrawResult.php
add     app/Models/LotteryTeam.php
add     app/Models/UserInvitation.php
add     app/Policies/LotteryPolicy.php
add     app/Services/LotteryConfigurationFactory.php
add     app/Services/LotteryConfigurationValidator.php
add     app/Services/SecureRandomNumberGenerator.php
add     app/Services/WeightedSinglePickDraw.php
add     app/Support/NormalizedEmail.php
add     app/Support/Percentage.php
add     config/invitations.php
add     config/platform.php
add     database/factories/LotteryDrawFactory.php
add     database/factories/LotteryDrawResultFactory.php
add     database/factories/LotteryFactory.php
add     database/factories/LotteryTeamFactory.php
add     database/factories/UserInvitationFactory.php
add     database/migrations/2026_07_18_000000_add_administration_fields_to_users_table.php
add     database/migrations/2026_07_18_000001_create_user_invitations_table.php
add     database/migrations/2026_07_18_000002_create_lotteries_table.php
add     database/migrations/2026_07_18_000003_create_lottery_teams_table.php
add     database/migrations/2026_07_18_000004_create_lottery_draws_table.php
add     database/migrations/2026_07_18_000005_create_lottery_draw_results_table.php
add     database/migrations/2026_07_18_000006_add_phase_six_query_indexes.php
add     database/migrations/2026_07_19_000007_add_presentation_mode_to_lotteries_table.php
add     database/seeders/DevelopmentSampleSeeder.php
add     database/seeders/PlatformAdministratorSeeder.php
add     docs/phase-5-browser-test-plan.md
add     docs/phase-6-manual-validation.md
add     docs/presentation-refinement-manual-validation.md
add     docs/publication-review.md
add     licenses/Laravel-MIT.txt
add     licenses/Lucide-LICENSE.txt
add     README.md
add     resources/js/lottery-odds-total.js
add     resources/js/lottery-presentation.js
add     resources/views/components/draft-order-list.blade.php
add     resources/views/components/empty-state.blade.php
add     resources/views/components/lottery-mode-badge.blade.php
add     resources/views/components/lottery-presentation.blade.php
add     resources/views/components/lottery-status-badge.blade.php
add     resources/views/components/odds-total.blade.php
add     resources/views/components/wordmark.blade.php
add     resources/views/index.blade.php
add     resources/views/livewire/admin/dashboard.blade.php
add     resources/views/livewire/admin/invitations/index.blade.php
add     resources/views/livewire/admin/users/index.blade.php
add     resources/views/livewire/auth/accept-invitation.blade.php
add     resources/views/livewire/lotteries/create-lottery.blade.php
add     resources/views/livewire/lotteries/edit-lottery.blade.php
add     resources/views/livewire/lotteries/index.blade.php
add     resources/views/livewire/lotteries/partials/form-fields.blade.php
add     resources/views/livewire/lotteries/show-lottery.blade.php
add     resources/views/mail/user-invitation.blade.php
add     routes/admin.php
add     tests/Feature/Admin/AdminAccessTest.php
add     tests/Feature/Admin/InvitationManagementTest.php
add     tests/Feature/Admin/UserManagementTest.php
add     tests/Feature/Auth/InvitationAcceptanceTest.php
add     tests/Feature/Auth/SuspendedUserTest.php
add     tests/Feature/LivewireRouteAuthorizationTest.php
add     tests/Feature/Lotteries/ArchiveLotteryTest.php
add     tests/Feature/Lotteries/CreateLotteryTest.php
add     tests/Feature/Lotteries/DrawDatabaseConstraintTest.php
add     tests/Feature/Lotteries/LotteryAuthorizationTest.php
add     tests/Feature/Lotteries/LotteryDrawLivewireTest.php
add     tests/Feature/Lotteries/LotteryLivewireTest.php
add     tests/Feature/Lotteries/LotteryModelTest.php
add     tests/Feature/Lotteries/LotteryPresentationUiTest.php
add     tests/Feature/Lotteries/LotteryReplayTest.php
add     tests/Feature/Lotteries/OfficialDrawTest.php
add     tests/Feature/Lotteries/SimulationDrawTest.php
add     tests/Feature/Lotteries/UpdateLotteryTest.php
add     tests/Feature/PhaseSixHardeningTest.php
add     tests/Unit/PercentageTest.php
add     tests/Unit/WeightedSinglePickDrawTest.php
add     THIRD_PARTY_NOTICES.md
delete  resources/views/welcome.blade.php
delete  resources/views/flux/navlist/group.blade.php
modify  .env.example
modify  .github/workflows/tests.yml
modify  .gitignore
modify  app/Actions/Fortify/CreateNewUser.php
modify  app/Concerns/ProfileValidationRules.php
modify  app/Livewire/Settings/Profile.php
modify  app/Models/User.php
modify  app/Providers/AppServiceProvider.php
modify  app/Providers/FortifyServiceProvider.php
modify  bootstrap/app.php
modify  composer.json
modify  composer.lock
modify  config/fortify.php
modify  database/factories/UserFactory.php
modify  database/seeders/DatabaseSeeder.php
modify  resources/css/app.css
modify  resources/js/app.js
modify  resources/views/components/app-logo.blade.php
modify  resources/views/components/settings/layout.blade.php
modify  resources/views/dashboard.blade.php
modify  resources/views/layouts/app.blade.php
modify  resources/views/layouts/app/sidebar.blade.php
modify  resources/views/layouts/auth/simple.blade.php
modify  resources/views/livewire/auth/login.blade.php
modify  resources/views/partials/head.blade.php
modify  routes/settings.php
modify  routes/web.php
modify  tests/Feature/Auth/AuthenticationTest.php
modify  tests/Feature/Auth/EmailVerificationTest.php
modify  tests/Feature/Auth/PasswordConfirmationTest.php
modify  tests/Feature/Auth/PasswordResetTest.php
modify  tests/Feature/Auth/RegistrationTest.php
modify  tests/Feature/Auth/TwoFactorChallengeTest.php
modify  tests/Feature/DashboardTest.php
modify  tests/Feature/ExampleTest.php
modify  tests/Feature/Settings/ProfileUpdateTest.php
modify  tests/Feature/Settings/SecurityTest.php
modify  tests/Pest.php
modify  tests/TestCase.php
modify  tests/Unit/ExampleTest.php
```
