# Billing and Multi-Tenancy

The application supports **multi-tenant** (SaaS) and **single-tenant** (internal) modes. See [Single-Tenant Mode](./single-tenant-mode.md) for switching.

## Payment gateways overview

| Gateway        | Use case              | Subscriptions | One-time / credits | Webhook route              | Config / docs                    |
|----------------|------------------------|---------------|--------------------|----------------------------|----------------------------------|
| **Stripe**     | Plans, seats, recurring| Yes           | Yes (e.g. credits) | `POST /webhooks/stripe`    | [Manage Stripe](../api-reference/routes.md), StripeSettings |
| **Paddle**     | Plans, recurring       | Yes           | —                  | `POST /webhooks/paddle`   | Paddle webhook + gateway config  |
| **Lemon Squeezy** | One-time products, credits | No        | Yes                | Package: `/lemon-squeezy/webhook` | [Lemon Squeezy](./lemon-squeezy.md), LemonSqueezySettings |

- **Subscriptions** (recurring plans, seat-based): use **Stripe** or **Paddle**. Stripe is the primary subscription gateway; Paddle is optional and wired for webhooks.
- **One-time purchases** (e.g. credit packs): use **Stripe** or **Lemon Squeezy**. Lemon Squeezy is documented in [Lemon Squeezy](./lemon-squeezy.md); the app listens to `OrderCreated` and adds credits via `AddCreditsFromLemonSqueezyOrder`.
- You can enable one or more gateways; per-organization overrides are supported via `ApplyOrganizationSettings` and the Billing settings group.

## Seat-Based Billing

Plans can be configured as per-seat (`is_per_seat`, `price_per_seat` on `plans`). Subscriptions store `quantity` and `gateway_subscription_id` on `plan_subscriptions`.

- **BillingSettings** (`App\Settings\BillingSettings`): `enable_seat_based_billing`, `allow_multiple_subscriptions`
- **SyncSubscriptionSeatsAction**: Syncs subscription quantity with organization member count when members are added/removed
- **Listeners**: `SyncSubscriptionSeatsOnMemberChange` reacts to `OrganizationMemberAdded` and `OrganizationMemberRemoved`
- **Payment gateway**: `PaymentGatewayInterface::updateSubscriptionQuantity()` updates Stripe, Lemon Squeezy (one-time only), or manual gateway when quantity changes. For credits one-time purchases via Lemon Squeezy, see [Lemon Squeezy](./lemon-squeezy.md).

## Domain / Subdomain Tenant Resolution

Tenant (organization) is resolved from the request domain:

- **ResolveDomainMiddleware**: Runs early in the web stack. Resolves organization from (1) exact match on `organization_domains.domain`, or (2) subdomain `{slug}.{base_domain}` when `TENANCY_SUBDOMAIN_RESOLUTION` is true
- **Config**: `config/tenancy.php` – `domain` (env `TENANCY_DOMAIN`), `subdomain_resolution` (env `TENANCY_SUBDOMAIN_RESOLUTION`)
- **Model**: `OrganizationDomain` – `organization_id`, `domain`, `type` (subdomain/custom), `is_verified`, `is_primary`

## Filament Tenant Scoping

`ScopesToCurrentTenant` trait ensures Filament resource queries filter by `organization_id` when `tenant_id()` is set. Super-admins bypass scoping. Used by `OrganizationInvitationResource`.

## Policies

- **CreditPolicy**, **RefundRequestPolicy**: Org-scoped access; users can only view/create for organizations they belong to.
