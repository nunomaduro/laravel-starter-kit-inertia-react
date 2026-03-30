# Email Templates

Email templates control the transactional emails your organization sends to users. Every event—from new user welcome messages to billing notifications—has a customizable template.

## How It Works

Each organization starts with default email templates. When you edit a template, a customized copy is created for your organization only. Other organizations continue using their defaults.

Changes to email templates affect only your organization and take effect immediately for all future emails.

## Default Email Events

The following events generate transactional emails:

| Event | Description | Available Variables |
|-------|-------------|-------------------|
| UserCreated | Welcome email sent to new users | `user.name`, `user.email`, `app.name`, `app.url` |
| OrganizationInvitationSent | Email sent when a user is invited to join your organization | `user.name`, `user.email`, `app.name`, `app.url` |
| OrganizationInvitationAccepted | Notification when an invitation is accepted | `user.name`, `user.email`, `app.name`, `app.url` |
| NewTermsVersionPublished | Notification when new terms of service are published | `app.name`, `app.url` |
| TrialEndingReminder | Warning sent when a trial period is about to expire | `app.name`, `app.url` |
| DunningFailedPaymentReminder | Notification when a payment fails | `app.name`, `app.url` |
| InvoicePaid | Receipt sent when an invoice is paid | `app.name`, `app.url` |

## Customizing Templates

### Edit an Email Template

1. Navigate to Settings → Email Templates
2. Click the event you want to customize from the list
3. Edit the **Subject** and **Body** fields
4. Use the variable buttons in the toolbar to insert variables, or type them directly: `{{ variable.name }}`
5. Click **Save** to apply your changes

### Preview Your Changes

After editing, click **Preview with Sample Data** to see how your email will look with placeholder values. This helps you verify formatting and ensure all variables are correctly positioned.

### Using Variables

Variables are placeholders that get replaced with actual data when the email is sent. For example:

- `{{ user.name }}` becomes the recipient's actual name
- `{{ app.name }}` becomes your organization's app name
- `{{ app.url }}` becomes your app's URL

Click the variable buttons in the editor toolbar for a list of available variables for that email event, or refer to the table above.

## Reset to Default

If you want to discard your customizations and return to the default template:

1. Open the email template you want to reset
2. Click **Reset to Default**
3. Confirm the action

Your customizations are permanently removed, and the default template is restored for future emails.

## Scope

All email template changes apply only to your organization. Users in other organizations will not see your customizations.

---

**Next:** [Learn about billing and subscriptions →](./billing.md)
