# API Reference

This document lists all available routes in the application.

**Last Updated**: 2026-02-14 (Pan product analytics, Filament Product Analytics page)

## Closure

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `filament/exports/{export}/download` | filament.exports.download | filament.actions |
| GET | `filament/imports/{import}/failed-rows/download` | filament.imports.failed-rows.download | filament.actions |
| GET | `admin/login` | filament.admin.auth.login | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| POST | `admin/logout` | filament.admin.auth.logout | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin` | filament.admin.pages.dashboard | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/user-activities-page` | filament.admin.pages.user-activities-page | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/manage-app` | filament.admin.pages.manage-app | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/manage-auth` | filament.admin.pages.manage-auth | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/manage-seo` | filament.admin.pages.manage-seo | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/analytics/product` | filament.admin.pages.analytics.product | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, … (Product Analytics page; see [Pan](backend/pan.md)) |
| GET | `admin/feature-segments` | filament.admin.resources.feature-segments.index | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/activity-logs` | filament.admin.resources.activity-logs.index | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/activity-logs/{record}` | filament.admin.resources.activity-logs.view | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/permissions` | filament.admin.resources.permissions.index | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/permissions/{record}` | filament.admin.resources.permissions.view | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/roles` | filament.admin.resources.roles.index | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/roles/create` | filament.admin.resources.roles.create | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/roles/{record}` | filament.admin.resources.roles.view | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/roles/{record}/edit` | filament.admin.resources.roles.edit | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/users` | filament.admin.resources.users.index | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/users/create` | filament.admin.resources.users.create | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/users/{record}` | filament.admin.resources.users.view | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/users/{record}/edit` | filament.admin.resources.users.edit | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| POST | `_boost/browser-logs` | boost.browser-logs | - |
| GET | `mcp/api` | - | - |
| POST | `mcp/api` | - | Laravel\Mcp\Server\Middleware\ReorderJsonAccept, Laravel\Mcp\Server\Middleware\AddWwwAuthenticateHeader, auth:sanctum |
| GET | `livewire-f0cf3e9a/js/{component}.js` | - | - |
| GET | `livewire-f0cf3e9a/css/{component}.css` | - | - |
| GET | `livewire-f0cf3e9a/css/{component}.global.css` | - | - |
| GET | `filament-excel/{path}` | filament-excel-download | web, signed |
| GET | `filament-impersonate/leave` | filament-impersonate.leave | web |
| GET | `api` | api | api |
| GET | `api/v1` | api.v1.info | api |
| GET | `favicon.ico` | favicon | web |
| GET | `robots.txt` | robots | web |
| GET | `/` | home | web |
| GET | `legal/terms` | legal.terms | web |
| GET | `legal/privacy` | legal.privacy | web |
| GET | `blog` | blog.index | web |
| GET | `blog/{post:slug}` | blog.show | web |
| GET | `changelog` | changelog.index | web |
| GET | `help` | help.index | web |
| GET | `help/{helpArticle:slug}` | help.show | web |
| POST | `help/{helpArticle:slug}/rate` | help.rate | web |
| GET | `dashboard` | dashboard | web, auth, verified |
| GET | `profile/export-pdf` | profile.export-pdf | web, auth, verified |
| GET, POST, PUT, PATCH, DELETE | `settings` | settings | web, auth |
| GET | `settings/appearance` | appearance.edit | web, auth |
| GET | `settings/achievements` | achievements.show | web, auth, feature:gamification |
| GET | `storage/{path}` | storage.local | - |
| GET | `docs/api` | scramble.docs.ui | web, Dedoc\Scramble\Http\Middleware\RestrictedDocsAccess |
| GET | `docs/api.json` | scramble.docs.document | web, Dedoc\Scramble\Http\Middleware\RestrictedDocsAccess |

## Pan (product analytics)

**Package**: panphp/pan. Events endpoint for client-side tracking. See [Pan (product analytics)](../backend/pan.md).

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| POST | `pan/events` | (Pan) | web |

## BlogController

**Controller**: `App\Http\Controllers\Blog\BlogController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `blog` | blog.index | web |
| GET | `blog/{post:slug}` | blog.show | web |

## ChangelogController

**Controller**: `App\Http\Controllers\Changelog\ChangelogController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `changelog` | changelog.index | web |

## HelpCenterController

**Controller**: `App\Http\Controllers\HelpCenter\HelpCenterController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `help` | help.index | web |
| GET | `help/{helpArticle:slug}` | help.show | web |

## RateHelpArticleController

**Controller**: `App\Http\Controllers\HelpCenter\RateHelpArticleController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| POST | `help/{helpArticle:slug}/rate` | help.rate | web |

## AchievementsController

**Controller**: `App\Http\Controllers\Settings\AchievementsController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `settings/achievements` | achievements.show | web, auth, feature:gamification |

## SessionController

**Controller**: `App\Http\Controllers\SessionController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `login` | login | web, guest |
| POST | `login` | login.store | web, guest |
| POST | `logout` | logout | web, auth |

### create

**Route**: `login`

**URI**: `login`

**Methods**: GET

**Middleware**: web, guest

**Method Parameters**:
- `request`: `Illuminate\Http\Request`

### store

**Route**: `login.store`

**URI**: `login`

**Methods**: POST

**Middleware**: web, guest

**Method Parameters**:
- `request`: `App\Http\Requests\CreateSessionRequest`

### destroy

**Route**: `logout`

**URI**: `logout`

**Methods**: POST

**Middleware**: web, auth

**Method Parameters**:
- `request`: `Illuminate\Http\Request`


## ConfirmablePasswordController

**Controller**: `Laravel\Fortify\Http\Controllers\ConfirmablePasswordController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `user/confirm-password` | password.confirm | web, auth:web |
| POST | `user/confirm-password` | password.confirm.store | web, auth:web |

### show

**Route**: `password.confirm`

**URI**: `user/confirm-password`

**Methods**: GET

**Middleware**: web, auth:web

**Method Parameters**:
- `request`: `Illuminate\Http\Request`

### store

**Route**: `password.confirm.store`

**URI**: `user/confirm-password`

**Methods**: POST

**Middleware**: web, auth:web

**Method Parameters**:
- `request`: `Illuminate\Http\Request`


## ConfirmedPasswordStatusController

**Controller**: `Laravel\Fortify\Http\Controllers\ConfirmedPasswordStatusController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `user/confirmed-password-status` | password.confirmation | web, auth:web |

### show

**Route**: `password.confirmation`

**URI**: `user/confirmed-password-status`

**Methods**: GET

**Middleware**: web, auth:web

**Method Parameters**:
- `request`: `Illuminate\Http\Request`


## TwoFactorAuthenticatedSessionController

**Controller**: `Laravel\Fortify\Http\Controllers\TwoFactorAuthenticatedSessionController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `two-factor-challenge` | two-factor.login | web, guest:web |
| POST | `two-factor-challenge` | two-factor.login.store | web, guest:web, throttle:two-factor |

### create

**Route**: `two-factor.login`

**URI**: `two-factor-challenge`

**Methods**: GET

**Middleware**: web, guest:web

**Method Parameters**:
- `request`: `Laravel\Fortify\Http\Requests\TwoFactorLoginRequest`

### store

**Route**: `two-factor.login.store`

**URI**: `two-factor-challenge`

**Methods**: POST

**Middleware**: web, guest:web, throttle:two-factor

**Method Parameters**:
- `request`: `Laravel\Fortify\Http\Requests\TwoFactorLoginRequest`


## TwoFactorAuthenticationController

**Controller**: `Laravel\Fortify\Http\Controllers\TwoFactorAuthenticationController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| POST | `user/two-factor-authentication` | two-factor.enable | web, auth:web, password.confirm |
| DELETE | `user/two-factor-authentication` | two-factor.disable | web, auth:web, password.confirm |

### store

**Route**: `two-factor.enable`

**URI**: `user/two-factor-authentication`

**Methods**: POST

**Middleware**: web, auth:web, password.confirm

**Method Parameters**:
- `request`: `Illuminate\Http\Request`
- `enable`: `Laravel\Fortify\Actions\EnableTwoFactorAuthentication`

### destroy

**Route**: `two-factor.disable`

**URI**: `user/two-factor-authentication`

**Methods**: DELETE

**Middleware**: web, auth:web, password.confirm

**Method Parameters**:
- `request`: `Illuminate\Http\Request`
- `disable`: `Laravel\Fortify\Actions\DisableTwoFactorAuthentication`


## ConfirmedTwoFactorAuthenticationController

**Controller**: `Laravel\Fortify\Http\Controllers\ConfirmedTwoFactorAuthenticationController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| POST | `user/confirmed-two-factor-authentication` | two-factor.confirm | web, auth:web, password.confirm |

### store

**Route**: `two-factor.confirm`

**URI**: `user/confirmed-two-factor-authentication`

**Methods**: POST

**Middleware**: web, auth:web, password.confirm

**Method Parameters**:
- `request`: `Illuminate\Http\Request`
- `confirm`: `Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication`


## TwoFactorQrCodeController

**Controller**: `Laravel\Fortify\Http\Controllers\TwoFactorQrCodeController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `user/two-factor-qr-code` | two-factor.qr-code | web, auth:web, password.confirm |

### show

**Route**: `two-factor.qr-code`

**URI**: `user/two-factor-qr-code`

**Methods**: GET

**Middleware**: web, auth:web, password.confirm

**Method Parameters**:
- `request`: `Illuminate\Http\Request`


## TwoFactorSecretKeyController

**Controller**: `Laravel\Fortify\Http\Controllers\TwoFactorSecretKeyController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `user/two-factor-secret-key` | two-factor.secret-key | web, auth:web, password.confirm |

### show

**Route**: `two-factor.secret-key`

**URI**: `user/two-factor-secret-key`

**Methods**: GET

**Middleware**: web, auth:web, password.confirm

**Method Parameters**:
- `request`: `Illuminate\Http\Request`


## RecoveryCodeController

**Controller**: `Laravel\Fortify\Http\Controllers\RecoveryCodeController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `user/two-factor-recovery-codes` | two-factor.recovery-codes | web, auth:web, password.confirm |
| POST | `user/two-factor-recovery-codes` | two-factor.regenerate-recovery-codes | web, auth:web, password.confirm |

### index

**Route**: `two-factor.recovery-codes`

**URI**: `user/two-factor-recovery-codes`

**Methods**: GET

**Middleware**: web, auth:web, password.confirm

**Method Parameters**:
- `request`: `Illuminate\Http\Request`

### store

**Route**: `two-factor.regenerate-recovery-codes`

**URI**: `user/two-factor-recovery-codes`

**Methods**: POST

**Middleware**: web, auth:web, password.confirm

**Method Parameters**:
- `request`: `Illuminate\Http\Request`
- `generate`: `Laravel\Fortify\Actions\GenerateNewRecoveryCodes`


## CsrfCookieController

**Controller**: `Laravel\Sanctum\Http\Controllers\CsrfCookieController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `sanctum/csrf-cookie` | sanctum.csrf-cookie | web |

### show

**Route**: `sanctum.csrf-cookie`

**URI**: `sanctum/csrf-cookie`

**Methods**: GET

**Middleware**: web

**Method Parameters**:
- `request`: `Illuminate\Http\Request`


## HandleRequests

**Controller**: `Livewire\Mechanisms\HandleRequests\HandleRequests`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| POST | `livewire-f0cf3e9a/update` | default.livewire.update | web |

### handleUpdate

**Route**: `default.livewire.update`

**URI**: `livewire-f0cf3e9a/update`

**Methods**: POST

**Middleware**: web


## FrontendAssets

**Controller**: `Livewire\Mechanisms\FrontendAssets\FrontendAssets`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `livewire-f0cf3e9a/livewire.js` | - | - |
| GET | `livewire-f0cf3e9a/livewire.min.js.map` | - | - |
| GET | `livewire-f0cf3e9a/livewire.csp.min.js.map` | - | - |

### returnJavaScriptAsFile

**Route**: ``

**URI**: `livewire-f0cf3e9a/livewire.js`

**Methods**: GET

### maps

**Route**: ``

**URI**: `livewire-f0cf3e9a/livewire.min.js.map`

**Methods**: GET

### cspMaps

**Route**: ``

**URI**: `livewire-f0cf3e9a/livewire.csp.min.js.map`

**Methods**: GET


## FileUploadController

**Controller**: `Livewire\Features\SupportFileUploads\FileUploadController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| POST | `livewire-f0cf3e9a/upload-file` | livewire.upload-file | web, throttle:60,1 |

### handle

**Route**: `livewire.upload-file`

**URI**: `livewire-f0cf3e9a/upload-file`

**Methods**: POST

**Middleware**: web, throttle:60,1


## FilePreviewController

**Controller**: `Livewire\Features\SupportFileUploads\FilePreviewController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `livewire-f0cf3e9a/preview-file/{filename}` | livewire.preview-file | web |

### handle

**Route**: `livewire.preview-file`

**URI**: `livewire-f0cf3e9a/preview-file/{filename}`

**Methods**: GET

**Parameters**:
- `filename`

**Middleware**: web

**Method Parameters**:
- `filename`: `mixed`


## UserController

**Controller**: `App\Http\Controllers\Api\V1\UserController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `api/v1/users` | api.v1.users.index | api, auth:sanctum |
| POST | `api/v1/users/batch` | api.v1.users.batch | api, auth:sanctum |
| POST | `api/v1/users/search` | api.v1.users.search | api, auth:sanctum |
| GET | `api/v1/users/{user}` | api.v1.users.show | api, auth:sanctum |
| POST | `api/v1/users` | api.v1.users.store | api, auth:sanctum |
| PUT, PATCH | `api/v1/users/{user}` | api.v1.users.update | api, auth:sanctum |
| DELETE | `api/v1/users/{user}` | api.v1.users.destroy | api, auth:sanctum |

### index

**Route**: `api.v1.users.index`

**URI**: `api/v1/users`

**Methods**: GET

**Middleware**: api, auth:sanctum

**Method Parameters**:
- `request`: `Illuminate\Http\Request`

### batch

**Route**: `api.v1.users.batch`

**URI**: `api/v1/users/batch`

**Methods**: POST

**Middleware**: api, auth:sanctum

**Method Parameters**:
- `request`: `App\Http\Requests\Api\V1\BatchUserRequest`
- `createUser`: `App\Actions\CreateUser`
- `updateUser`: `App\Actions\UpdateUser`
- `deleteUser`: `App\Actions\DeleteUser`

### search

**Route**: `api.v1.users.search`

**URI**: `api/v1/users/search`

**Methods**: POST

**Middleware**: api, auth:sanctum

**Method Parameters**:
- `request`: `App\Http\Requests\Api\V1\SearchUserRequest`

### show

**Route**: `api.v1.users.show`

**URI**: `api/v1/users/{user}`

**Methods**: GET

**Parameters**:
- `user`

**Middleware**: api, auth:sanctum

**Method Parameters**:
- `user`: `App\Models\User`

### store

**Route**: `api.v1.users.store`

**URI**: `api/v1/users`

**Methods**: POST

**Middleware**: api, auth:sanctum

**Method Parameters**:
- `request`: `App\Http\Requests\CreateUserRequest`
- `action`: `App\Actions\CreateUser`

### update

**Route**: `api.v1.users.update`

**URI**: `api/v1/users/{user}`

**Methods**: PUT, PATCH

**Parameters**:
- `user`

**Middleware**: api, auth:sanctum

**Method Parameters**:
- `request`: `App\Http\Requests\UpdateUserRequest`
- `user`: `App\Models\User`
- `action`: `App\Actions\UpdateUser`

### destroy

**Route**: `api.v1.users.destroy`

**URI**: `api/v1/users/{user}`

**Methods**: DELETE

**Parameters**:
- `user`

**Middleware**: api, auth:sanctum

**Method Parameters**:
- `request`: `App\Http\Requests\DeleteUserRequest`
- `user`: `App\Models\User`
- `action`: `App\Actions\DeleteUser`


## Impersonation (filament-impersonate)

Impersonation is started from the Filament admin (Users table or Edit User page) via the package action. The package registers:

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `filament-impersonate/leave` | filament-impersonate.leave | web |

See [Filament > User impersonation](../backend/filament.md#user-impersonation).

## UserController

**Controller**: `App\Http\Controllers\UserController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| DELETE | `user` | user.destroy | web, auth |
| GET | `register` | register | web, guest |
| POST | `register` | register.store | web, guest, Spatie\Honeypot\ProtectAgainstSpam |

### destroy

**Route**: `user.destroy`

**URI**: `user`

**Methods**: DELETE

**Middleware**: web, auth

**Method Parameters**:
- `request`: `App\Http\Requests\DeleteUserRequest`
- `user`: `App\Models\User`
- `action`: `App\Actions\DeleteUser`

### create

**Route**: `register`

**URI**: `register`

**Methods**: GET

**Middleware**: web, guest

### store

**Route**: `register.store`

**URI**: `register`

**Methods**: POST

**Middleware**: web, guest, Spatie\Honeypot\ProtectAgainstSpam

**Method Parameters**:
- `request`: `App\Http\Requests\CreateUserRequest`
- `action`: `App\Actions\CreateUser`


## UserProfileController

**Controller**: `App\Http\Controllers\UserProfileController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `settings/profile` | user-profile.edit | web, auth |
| PATCH | `settings/profile` | user-profile.update | web, auth |

### edit

**Route**: `user-profile.edit`

**URI**: `settings/profile`

**Methods**: GET

**Middleware**: web, auth

**Method Parameters**:
- `request`: `Illuminate\Http\Request`

### update

**Route**: `user-profile.update`

**URI**: `settings/profile`

**Methods**: PATCH

**Middleware**: web, auth

**Method Parameters**:
- `request`: `App\Http\Requests\UpdateUserRequest`
- `user`: `App\Models\User`
- `action`: `App\Actions\UpdateUser`


## UserPasswordController

**Controller**: `App\Http\Controllers\UserPasswordController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `settings/password` | password.edit | web, auth |
| PUT | `settings/password` | password.update | web, auth, throttle:6,1 |
| GET | `reset-password/{token}` | password.reset | web, guest |
| POST | `reset-password` | password.store | web, guest |

### edit

**Route**: `password.edit`

**URI**: `settings/password`

**Methods**: GET

**Middleware**: web, auth

### update

**Route**: `password.update`

**URI**: `settings/password`

**Methods**: PUT

**Middleware**: web, auth, throttle:6,1

**Method Parameters**:
- `request`: `App\Http\Requests\UpdateUserPasswordRequest`
- `user`: `App\Models\User`
- `action`: `App\Actions\UpdateUserPassword`

### create

**Route**: `password.reset`

**URI**: `reset-password/{token}`

**Methods**: GET

**Parameters**:
- `token`

**Middleware**: web, guest

**Method Parameters**:
- `request`: `Illuminate\Http\Request`

### store

**Route**: `password.store`

**URI**: `reset-password`

**Methods**: POST

**Middleware**: web, guest

**Method Parameters**:
- `request`: `App\Http\Requests\CreateUserPasswordRequest`
- `action`: `App\Actions\CreateUserPassword`


## UserTwoFactorAuthenticationController

**Controller**: `App\Http\Controllers\UserTwoFactorAuthenticationController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `settings/two-factor` | two-factor.show | web, auth, password.confirm |

### show

**Route**: `two-factor.show`

**URI**: `settings/two-factor`

**Methods**: GET

**Middleware**: web, auth, password.confirm

**Method Parameters**:
- `request`: `App\Http\Requests\ShowUserTwoFactorAuthenticationRequest`
- `user`: `App\Models\User`


## UserEmailResetNotificationController

**Controller**: `App\Http\Controllers\UserEmailResetNotificationController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `forgot-password` | password.request | web, guest |
| POST | `forgot-password` | password.email | web, guest |

### create

**Route**: `password.request`

**URI**: `forgot-password`

**Methods**: GET

**Middleware**: web, guest

**Method Parameters**:
- `request`: `Illuminate\Http\Request`

### store

**Route**: `password.email`

**URI**: `forgot-password`

**Methods**: POST

**Middleware**: web, guest

**Method Parameters**:
- `request`: `App\Http\Requests\CreateUserEmailResetNotificationRequest`
- `action`: `App\Actions\CreateUserEmailResetNotification`


## UserEmailVerificationNotificationController

**Controller**: `App\Http\Controllers\UserEmailVerificationNotificationController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `verify-email` | verification.notice | web, auth |
| POST | `email/verification-notification` | verification.send | web, auth, throttle:6,1 |

### create

**Route**: `verification.notice`

**URI**: `verify-email`

**Methods**: GET

**Middleware**: web, auth

**Method Parameters**:
- `request`: `Illuminate\Http\Request`
- `user`: `App\Models\User`

### store

**Route**: `verification.send`

**URI**: `email/verification-notification`

**Methods**: POST

**Middleware**: web, auth, throttle:6,1

**Method Parameters**:
- `user`: `App\Models\User`
- `action`: `App\Actions\CreateUserEmailVerificationNotification`


## UserEmailVerificationController

**Controller**: `App\Http\Controllers\UserEmailVerificationController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `verify-email/{id}/{hash}` | verification.verify | web, auth, signed |

### update

**Route**: `verification.verify`

**URI**: `verify-email/{id}/{hash}`

**Methods**: GET

**Parameters**:
- `id`
- `hash`

**Middleware**: web, auth, signed, throttle:6,1

**Method Parameters**:
- `request`: `Illuminate\Foundation\Auth\EmailVerificationRequest`
- `user`: `App\Models\User`


## PricingController

**Controller**: `App\Http\Controllers\Billing\PricingController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `pricing` | pricing | web |


## InvitationAcceptController

**Controller**: `App\Http\Controllers\InvitationAcceptController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `invitations/{token}` | invitations.show | web |
| POST | `invitations/{token}/accept` | invitations.accept | web, auth |


## OrganizationController

**Controller**: `App\Http\Controllers\Organization\OrganizationController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `organizations` | organizations.index | web, auth, verified |
| GET | `organizations/create` | organizations.create | web, auth, verified |
| POST | `organizations` | organizations.store | web, auth, verified |
| GET | `organizations/{organization}` | organizations.show | web, auth, verified |
| PUT, PATCH | `organizations/{organization}` | organizations.update | web, auth, verified |
| DELETE | `organizations/{organization}` | organizations.destroy | web, auth, verified |
| GET | `organizations/{organization}/edit` | organizations.edit | web, auth, verified |


## OrganizationMemberController

**Controller**: `App\Http\Controllers\Organization\OrganizationMemberController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `organizations/{organization}/members` | organizations.members.index | web, auth, verified |
| PUT | `organizations/{organization}/members/{member}` | organizations.members.update | web, auth, verified |
| DELETE | `organizations/{organization}/members/{member}` | organizations.members.destroy | web, auth, verified |


## OrganizationInvitationController

**Controller**: `App\Http\Controllers\Organization\OrganizationInvitationController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| POST | `organizations/{organization}/invitations` | organizations.invitations.store | web, auth, verified |
| DELETE | `organizations/{organization}/invitations/{invitation}` | organizations.invitations.destroy | web, auth, verified |
| PUT | `organizations/{organization}/invitations/{invitation}/resend` | organizations.invitations.resend | web, auth, verified |


## OrganizationSwitchController

**Controller**: `App\Http\Controllers\Organization\OrganizationSwitchController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| POST | `organizations/switch` | organizations.switch | web, auth, verified |


## BillingDashboardController

**Controller**: `App\Http\Controllers\Billing\BillingDashboardController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `billing` | billing.index | web, auth, verified, tenant |


## CreditController

**Controller**: `App\Http\Controllers\Billing\CreditController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `billing/credits` | billing.credits.index | web, auth, verified, tenant |
| POST | `billing/credits/purchase` | billing.credits.purchase | web, auth, verified, tenant |
| POST | `billing/credits/checkout/lemon-squeezy` | billing.credits.checkout.lemon-squeezy | web, auth, verified, tenant |


## InvoiceController

**Controller**: `App\Http\Controllers\Billing\InvoiceController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `billing/invoices` | billing.invoices.index | web, auth, verified, tenant |
| GET | `billing/invoices/{invoice}` | billing.invoices.download | web, auth, verified, tenant |


## StripeWebhookController

**Controller**: `App\Http\Controllers\Billing\StripeWebhookController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| POST | `webhooks/stripe` | webhooks.stripe | web (CSRF excluded) |

## Lemon Squeezy Webhook (lemonsqueezy/laravel)

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| POST | `lemon-squeezy/webhook` | lemon-squeezy.webhook | web (CSRF excluded) |

