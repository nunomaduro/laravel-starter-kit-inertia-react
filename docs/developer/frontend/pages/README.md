# Pages

Inertia pages are React components that receive data from Laravel controllers. They live in `resources/js/pages/`.

## Layout conventions

**Authenticated app pages (dashboard, modules, settings, billing, organizations, etc.) must use the same layout** so the UI is consistent:

- Use **`AppLayout`** from `@/layouts/app-layout` for any page that should show the sidebar, top bar, and breadcrumbs.
- Pass **`breadcrumbs`**: an array of `{ title: string, href: string }` (e.g. Dashboard → Module → optional current page).
- Wrap page content in the same content wrapper used elsewhere:  
  `<div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">`.

**Do not** use a custom full-page layout (e.g. standalone header + “Back to home”) for app modules. Reserve that for unauthenticated or one-off flows (e.g. welcome, login, legal pages).

Examples: dashboard, blog (index/show), changelog, help (index/show), billing, organizations, settings — all use `AppLayout`.

## Available Pages

| Page | Documented |
|------|------|
| [appearance/update](./appearance-update.md) | Yes |
| [dashboard](./dashboard.md) | Yes |
| [session/create](./session-create.md) | Yes |
| [user-email-reset-notification/create](./user-email-reset-notification-create.md) | Yes |
| [user-email-verification-notification/create](./user-email-verification-notification-create.md) | Yes |
| [user-password-confirmation/create](./user-password-confirmation-create.md) | Yes |
| [user-password/create](./user-password-create.md) | Yes |
| [user-password/edit](./user-password-edit.md) | Yes |
| [user-profile/edit](./user-profile-edit.md) | Yes |
| [user-two-factor-authentication-challenge/show](./user-two-factor-authentication-challenge-show.md) | Yes |
| [user-two-factor-authentication/show](./user-two-factor-authentication-show.md) | Yes |
| [user/create](./user-create.md) | Yes |
| [welcome](./welcome.md) | Yes |
| [contact/create](./contact-create.md) | Yes |
| [blog/index](./blog-index.md) | Yes |
| [blog/show](./blog-show.md) | Yes |
| [changelog/index](./changelog-index.md) | Yes |
| [help/index](./help-index.md) | Yes |
| [help/show](./help-show.md) | Yes |
| [settings/personal-data-export](./settings-personal-data-export.md) | Yes |
| [onboarding/show](./onboarding-show.md) | Yes |
| [legal/privacy](./legal-privacy.md) | Yes |
| [legal/terms](./legal-terms.md) | Yes |
| [settings/achievements](docs/developer/frontend/pages/settings/achievements.md) | Yes |
| [invitations/accept](./invitations/accept.md) | Yes |
| [organizations/create](./organizations/create.md) | Yes |
| [organizations/index](./organizations/index.md) | Yes |
| [organizations/members](./organizations/members.md) | Yes |
| [organizations/show](./organizations/show.md) | Yes |
| [billing/credits](./billing/credits.md) | Yes |
| [billing/index](./billing/index.md) | Yes |
| [billing/invoices](./billing/invoices.md) | Yes |
| [pricing](./pricing.md) | Yes |
| [terms/accept](./terms-accept.md) | Yes |
| [enterprise-inquiries/create](./enterprise-inquiries-create.md) | Yes |
| [chat/index](./chat/index.md) | Yes |
| [user-table](./user-table.md) | Yes |
| [users/table](./users/table.md) | Yes |
| [users/show](./users/show.md) | Yes |
| [pages/edit](./pages/edit.md) | Yes |
| [pages/index](./pages/index.md) | Yes |
| [pages/show](./pages/show.md) | Yes |
| [settings/branding](./settings/branding.md) | Yes |
| [dev/components](docs/developer/frontend/pages/dev/components.md) | Yes |
| dev/pages | Yes |
| error | Yes |
| [settings/audit-log](docs/developer/frontend/pages/settings/audit-log.md) | Yes |
| settings/features | Yes |
| settings/roles | Yes |
| [settings/domains](docs/developer/frontend/pages/settings/domains.md) | Yes |
| [settings/general](docs/developer/frontend/pages/settings/general.md) | Yes |
| [settings/notifications](docs/developer/frontend/pages/settings/notifications.md) | Yes |
| notifications/index | Yes |
| [announcements/table](./announcements/table.md) | Yes |
| [categories/table](./categories/table.md) | Yes |
| [organizations/table](./organizations/table.md) | Yes |
| [posts/table](./posts/table.md) | Yes |
| [reports/edit](./reports/edit.md) | Yes |
| [reports/index](./reports/index.md) | Yes |
| [reports/show](./reports/show.md) | Yes |
| [dashboards/edit](./dashboards/edit.md) | Yes |
| [dashboards/index](./dashboards/index.md) | Yes |
| [dashboards/show](./dashboards/show.md) | Yes |
| [showcase/index](./showcase/index.md) | Yes |
| [crm/contacts/create](./crm/contacts/create.md) | Yes |
| [crm/contacts/edit](./crm/contacts/edit.md) | Yes |
| [crm/contacts/index](./crm/contacts/index.md) | Yes |
| [crm/deals/index](./crm/deals/index.md) | Yes |
| [hr/employees/create](./hr/employees/create.md) | Yes |
| [hr/employees/edit](./hr/employees/edit.md) | Yes |
| [hr/employees/index](./hr/employees/index.md) | Yes |
| [hr/leave-requests/index](./hr/leave-requests/index.md) | Yes |
| [wizard/index](./wizard/index.md) | Yes |
| [users/columns](./users/columns.md) | Yes |
| [users/table-toolbar](./users/table-toolbar.md) | Yes |
| [welcome/hero-section](./welcome/hero-section.md) | Yes |
| [welcome/welcome-header](./welcome/welcome-header.md) | Yes |
| [welcome/built-with-section](./welcome/built-with-section.md) | Yes |
| [welcome/how-it-works-section](./welcome/how-it-works-section.md) | Yes |
| [welcome/modules-section](./welcome/modules-section.md) | Yes |
| [welcome/stats-section](./welcome/stats-section.md) | Yes |
| [welcome/differentiators-section](./welcome/differentiators-section.md) | Yes |
| [welcome/features-section](./welcome/features-section.md) | Yes |
| [welcome/comparison-section](./welcome/comparison-section.md) | Yes |
| [welcome/cta-section](./welcome/cta-section.md) | Yes |
| [welcome/pricing-section](./welcome/pricing-section.md) | Yes |
| [welcome/welcome-footer](./welcome/welcome-footer.md) | Yes |
| [bot-studio/create](./bot-studio/create.md) | Yes |
| [bot-studio/edit](./bot-studio/edit.md) | Yes |
| [bot-studio/index](./bot-studio/index.md) | Yes |
| [bot-studio/templates](./bot-studio/templates.md) | Yes |
| [bot-studio/marketplace/index](./bot-studio/marketplace/index.md) | Yes |
| [bot-studio/marketplace/show](./bot-studio/marketplace/show.md) | Yes |
| categories/create | No |
| categories/edit | No |
| invitations/create | No |
| invitations/index | No |
| users/create | No |
| users/edit | No |


