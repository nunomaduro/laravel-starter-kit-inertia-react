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

| Page | Route | Documented |
|------|-------|------------|
| [appearance/update](./appearance-update.md) | N/A | ✅ |
| [dashboard](./dashboard.md) | dashboard | ✅ |
| [session/create](./session-create.md) | login, login.store | ✅ |
| [user-email-reset-notification/create](./user-email-reset-notification-create.md) | password.request, password.email | ✅ |
| [user-email-verification-notification/create](./user-email-verification-notification-create.md) | verification.notice, verification.send | ✅ |
| [user-password-confirmation/create](./user-password-confirmation-create.md) | N/A | ✅ |
| [user-password/create](./user-password-create.md) | password.reset, password.store | ✅ |
| [user-password/edit](./user-password-edit.md) | password.reset, password.store | ✅ |
| [user-profile/edit](./user-profile-edit.md) | user-profile.edit, user-profile.update | ✅ |
| [user-two-factor-authentication-challenge/show](./user-two-factor-authentication-challenge-show.md) | N/A | ✅ |
| [user-two-factor-authentication/show](./user-two-factor-authentication-show.md) | two-factor.show | ✅ |
| [user/create](./user-create.md) | user.destroy, register | ✅ |
| [welcome](./welcome.md) | mails.webhook, filament.exports.download | ✅ |
| [contact/create](./contact-create.md) | contact.create, contact.store | ✅ |
| [blog/index](./blog-index.md) | blog.index, blog.show | ✅ |
| [blog/show](./blog-show.md) | blog.index, blog.show | ✅ |
| [changelog/index](./changelog-index.md) | changelog.index | ✅ |
| [help/index](./help-index.md) | help.index, help.show | ✅ |
| [help/show](./help-show.md) | help.index, help.show | ✅ |
| [settings/personal-data-export](./settings-personal-data-export.md) | N/A | ✅ |
| [onboarding/show](./onboarding-show.md) | onboarding, onboarding.store | ✅ |
| [legal/privacy](./legal-privacy.md) | mails.webhook, filament.exports.download | ✅ |
| [legal/terms](./legal-terms.md) | mails.webhook, filament.exports.download | ✅ |
| [settings/achievements](docs/developer/frontend/pages/settings/achievements.md) | achievements.show | ✅ |
| [invitations/accept](./invitations/accept.md) | invitations.show, invitations.accept | ✅ |
| [organizations/create](./organizations/create.md) | organizations.index, organizations.create | ✅ |
| [organizations/index](./organizations/index.md) | organizations.index, organizations.create | ✅ |
| [organizations/members](./organizations/members.md) | organizations.members.index, organizations.members.update | ✅ |
| [organizations/show](./organizations/show.md) | organizations.index, organizations.create | ✅ |
| [billing/credits](./billing/credits.md) | billing.credits.index, billing.credits.purchase | ✅ |
| [billing/index](./billing/index.md) | billing.index | ✅ |
| [billing/invoices](./billing/invoices.md) | billing.invoices.index, billing.invoices.download | ✅ |
| [pricing](./pricing.md) | pricing | ✅ |
| [terms/accept](./terms-accept.md) | terms.accept, terms.accept.store | ✅ |
| [enterprise-inquiries/create](./enterprise-inquiries-create.md) | enterprise-inquiries.create, enterprise-inquiries.store | ✅ |
| [chat/index](./chat/index.md) | mails.webhook, filament.exports.download | ✅ |
| [user-table](./user-table.md) | N/A | ✅ |
| [users/table](./users/table.md) | users.table, users.bulk-soft-delete | ✅ |
| [users/show](./users/show.md) | users.table, users.bulk-soft-delete | ✅ |
| [pages/edit](./pages/edit.md) | pages.index, pages.create | ✅ |
| [pages/index](./pages/index.md) | pages.index, pages.create | ✅ |
| [pages/show](./pages/show.md) | pages.index, pages.create | ✅ |
| [settings/branding](./settings/branding.md) | settings.branding.edit, settings.branding.update | ✅ |
| [dev/components](docs/developer/frontend/pages/dev/components.md) | dev.components | ✅ |
| dev/pages | dev.pages | ✅ |
| error | N/A | ✅ |
| [settings/audit-log](docs/developer/frontend/pages/settings/audit-log.md) | settings.audit-log | ✅ |
| settings/features | settings.features.show, settings.features.update | ✅ |
| settings/roles | settings.roles.index, settings.roles.store | ✅ |
| [settings/domains](docs/developer/frontend/pages/settings/domains.md) | settings.domains.show, settings.domains.store | ✅ |
| [settings/general](docs/developer/frontend/pages/settings/general.md) | settings.general.show, settings.general.slug.update | ✅ |
| [settings/notifications](docs/developer/frontend/pages/settings/notifications.md) | settings.notifications.show, settings.notifications.update | ✅ |
| notifications/index | notifications.index | ✅ |
| [announcements/table](./announcements/table.md) | announcements.table | ✅ |
| [categories/table](./categories/table.md) | categories.table | ✅ |
| [organizations/table](./organizations/table.md) | organizations.list | ✅ |
| [posts/table](./posts/table.md) | posts.table | ✅ |
| [reports/edit](./reports/edit.md) | reports.index, reports.create | ✅ |
| [reports/index](./reports/index.md) | reports.index, reports.create | ✅ |
| [reports/show](./reports/show.md) | reports.index, reports.create | ✅ |
| [dashboards/edit](./dashboards/edit.md) | dashboards.index, dashboards.create | ✅ |
| [dashboards/index](./dashboards/index.md) | dashboards.index, dashboards.create | ✅ |
| [dashboards/show](./dashboards/show.md) | dashboards.index, dashboards.create | ✅ |
| [showcase/index](./showcase/index.md) | mails.webhook, filament.exports.download | ✅ |
| [crm/contacts/create](./crm/contacts/create.md) | crm.contacts.index, crm.contacts.create | ✅ |
| [crm/contacts/edit](./crm/contacts/edit.md) | crm.contacts.index, crm.contacts.create | ✅ |
| [crm/contacts/index](./crm/contacts/index.md) | crm.contacts.index, crm.contacts.create | ✅ |
| [crm/deals/index](./crm/deals/index.md) | crm.deals.index, crm.deals.create | ✅ |
| [hr/employees/create](./hr/employees/create.md) | hr.employees.index, hr.employees.create | ✅ |
| [hr/employees/edit](./hr/employees/edit.md) | hr.employees.index, hr.employees.create | ✅ |
| [hr/employees/index](./hr/employees/index.md) | hr.employees.index, hr.employees.create | ✅ |
| [hr/leave-requests/index](./hr/leave-requests/index.md) | hr.leave-requests.index, hr.leave-requests.create | ✅ |


