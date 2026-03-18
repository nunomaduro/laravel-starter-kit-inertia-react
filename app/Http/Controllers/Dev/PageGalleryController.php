<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dev;

use Inertia\Inertia;
use Inertia\Response;

final class PageGalleryController
{
    public function __invoke(): Response
    {
        return Inertia::render('dev/pages', [
            'templates' => $this->templates(),
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function templates(): array
    {
        return [
            // ─── App ────────────────────────────────────────────────────────
            [
                'id' => 'dashboard',
                'name' => 'Dashboard',
                'category' => 'App',
                'description' => 'Main app dashboard with KPI stats, weekly activity area chart, and onboarding progress card for new users.',
                'route' => 'dashboard',
                'url' => route('dashboard'),
                'component' => 'pages/dashboard.tsx',
                'controller' => 'app/Http/Controllers/DashboardController.php',
                'tags' => ['stats', 'charts', 'onboarding', 'overview'],
                'guestOnly' => false,
                'color' => 'blue',
            ],
            [
                'id' => 'chat',
                'name' => 'Chat',
                'category' => 'App',
                'description' => 'Real-time AI chat interface with streaming responses via Laravel Reverb WebSocket.',
                'route' => 'chat',
                'url' => route('chat'),
                'component' => 'pages/chat/index.tsx',
                'controller' => null,
                'tags' => ['chat', 'ai', 'realtime', 'websocket'],
                'guestOnly' => false,
                'color' => 'green',
            ],

            // ─── Users ──────────────────────────────────────────────────────
            [
                'id' => 'users-list',
                'name' => 'Users List',
                'category' => 'Users',
                'description' => 'Server-side data table for browsing and managing all users, with bulk actions, filters, and column sorting.',
                'route' => 'users.table',
                'url' => route('users.table'),
                'component' => 'pages/users/list.tsx',
                'controller' => 'app/Http/Controllers/UsersTableController.php',
                'tags' => ['table', 'datatable', 'users', 'bulk-actions'],
                'guestOnly' => false,
                'color' => 'purple',
            ],
            [
                'id' => 'users-show',
                'name' => 'User Profile',
                'category' => 'Users',
                'description' => 'Detailed user profile page showing account info, activity, roles, and permissions.',
                'route' => 'users.show',
                'url' => null,
                'component' => 'pages/users/show.tsx',
                'controller' => 'app/Http/Controllers/UsersTableController.php',
                'tags' => ['profile', 'user', 'detail', 'admin'],
                'guestOnly' => false,
                'color' => 'purple',
            ],

            // ─── Billing ────────────────────────────────────────────────────
            [
                'id' => 'billing-overview',
                'name' => 'Billing Overview',
                'category' => 'Billing',
                'description' => 'Billing dashboard with current plan, credit balance, subscription details, and quick upgrade CTA.',
                'route' => 'billing.index',
                'url' => route('billing.index'),
                'component' => 'pages/billing/index.tsx',
                'controller' => 'app/Http/Controllers/Billing/BillingDashboardController.php',
                'tags' => ['billing', 'subscription', 'plan', 'credits'],
                'guestOnly' => false,
                'color' => 'amber',
            ],
            [
                'id' => 'billing-invoices',
                'name' => 'Invoices',
                'category' => 'Billing',
                'description' => 'Invoice history table with status badges, download links, and an empty state for new accounts.',
                'route' => 'billing.invoices.index',
                'url' => route('billing.invoices.index'),
                'component' => 'pages/billing/invoices.tsx',
                'controller' => 'app/Http/Controllers/Billing/InvoiceController.php',
                'tags' => ['billing', 'invoices', 'table', 'download'],
                'guestOnly' => false,
                'color' => 'amber',
            ],
            [
                'id' => 'billing-credits',
                'name' => 'Credits',
                'category' => 'Billing',
                'description' => 'Credit balance management page with purchase options and usage history.',
                'route' => 'billing.credits.index',
                'url' => route('billing.credits.index'),
                'component' => 'pages/billing/credits.tsx',
                'controller' => 'app/Http/Controllers/Billing/CreditController.php',
                'tags' => ['billing', 'credits', 'purchase'],
                'guestOnly' => false,
                'color' => 'amber',
            ],

            // ─── Settings ───────────────────────────────────────────────────
            [
                'id' => 'settings-profile',
                'name' => 'Profile Settings',
                'category' => 'Settings',
                'description' => 'User profile settings with avatar upload, name/email edit, danger zone, and help text on fields.',
                'route' => 'user-profile.edit',
                'url' => route('user-profile.edit'),
                'component' => 'pages/user-profile/edit.tsx',
                'controller' => 'app/Http/Controllers/UserProfileController.php',
                'tags' => ['settings', 'profile', 'avatar', 'danger-zone'],
                'guestOnly' => false,
                'color' => 'gray',
            ],
            [
                'id' => 'settings-password',
                'name' => 'Password Settings',
                'category' => 'Settings',
                'description' => 'Password change form with current password verification and strength requirements.',
                'route' => 'password.edit',
                'url' => route('password.edit'),
                'component' => 'pages/settings/password.tsx',
                'controller' => 'app/Http/Controllers/UserPasswordController.php',
                'tags' => ['settings', 'password', 'security'],
                'guestOnly' => false,
                'color' => 'gray',
            ],
            [
                'id' => 'settings-appearance',
                'name' => 'Appearance',
                'category' => 'Settings',
                'description' => 'Theme customization: light/dark/system mode, primary color, border radius, and skin selector.',
                'route' => 'appearance.edit',
                'url' => route('appearance.edit'),
                'component' => 'pages/appearance/update.tsx',
                'controller' => null,
                'tags' => ['settings', 'theme', 'dark-mode', 'appearance'],
                'guestOnly' => false,
                'color' => 'gray',
            ],
            [
                'id' => 'settings-branding',
                'name' => 'Org Branding',
                'category' => 'Settings',
                'description' => 'Organization branding settings: logo, primary color, and custom CSS per tenant.',
                'route' => 'settings.branding.edit',
                'url' => route('settings.branding.edit'),
                'component' => 'pages/settings/branding.tsx',
                'controller' => 'app/Http/Controllers/Settings/BrandingController.php',
                'tags' => ['settings', 'branding', 'logo', 'multi-tenant'],
                'guestOnly' => false,
                'color' => 'gray',
            ],

            // ─── Organizations ──────────────────────────────────────────────
            [
                'id' => 'organizations-list',
                'name' => 'Organizations',
                'category' => 'Organizations',
                'description' => 'Multi-tenant organization list with create/switch/edit actions and member counts.',
                'route' => 'organizations.index',
                'url' => route('organizations.index'),
                'component' => 'pages/organizations/index.tsx',
                'controller' => 'app/Http/Controllers/OrganizationController.php',
                'tags' => ['organizations', 'multi-tenant', 'list'],
                'guestOnly' => false,
                'color' => 'cyan',
            ],

            // ─── Onboarding ─────────────────────────────────────────────────
            [
                'id' => 'onboarding',
                'name' => 'Onboarding',
                'category' => 'Onboarding',
                'description' => 'Step-by-step onboarding wizard for new users: profile, organization, invite team.',
                'route' => 'onboarding',
                'url' => route('onboarding'),
                'component' => 'pages/onboarding.tsx',
                'controller' => 'app/Http/Controllers/OnboardingController.php',
                'tags' => ['onboarding', 'wizard', 'new-user'],
                'guestOnly' => false,
                'color' => 'emerald',
            ],

            // ─── Auth ───────────────────────────────────────────────────────
            [
                'id' => 'auth-login',
                'name' => 'Login',
                'category' => 'Auth',
                'description' => 'Clean login form with email/password, remember me, social auth links, and forgot password.',
                'route' => 'login',
                'url' => null,
                'component' => 'pages/auth/login.tsx',
                'controller' => 'app/Http/Controllers/SessionController.php',
                'tags' => ['auth', 'login', 'form'],
                'guestOnly' => true,
                'color' => 'rose',
            ],
            [
                'id' => 'auth-register',
                'name' => 'Register',
                'category' => 'Auth',
                'description' => 'Registration form with name, email, password, and terms acceptance checkbox.',
                'route' => 'register',
                'url' => null,
                'component' => 'pages/auth/register.tsx',
                'controller' => 'app/Http/Controllers/UserController.php',
                'tags' => ['auth', 'register', 'form'],
                'guestOnly' => true,
                'color' => 'rose',
            ],
            [
                'id' => 'auth-forgot-password',
                'name' => 'Forgot Password',
                'category' => 'Auth',
                'description' => 'Password reset request form with email input and success state.',
                'route' => 'password.request',
                'url' => null,
                'component' => 'pages/auth/forgot-password.tsx',
                'controller' => 'app/Http/Controllers/UserEmailResetNotificationController.php',
                'tags' => ['auth', 'password-reset', 'form'],
                'guestOnly' => true,
                'color' => 'rose',
            ],

            // ─── Marketing ──────────────────────────────────────────────────
            [
                'id' => 'marketing-landing',
                'name' => 'Landing Page',
                'category' => 'Marketing',
                'description' => 'Public marketing landing page with hero, social proof, feature highlights, and CTA sections.',
                'route' => 'home',
                'url' => route('home'),
                'component' => 'pages/welcome.tsx',
                'controller' => null,
                'tags' => ['marketing', 'landing', 'hero', 'public'],
                'guestOnly' => false,
                'color' => 'indigo',
            ],
            [
                'id' => 'marketing-pricing',
                'name' => 'Pricing',
                'category' => 'Marketing',
                'description' => 'Pricing table with plan cards, feature comparison, and Stripe/Lemon Squeezy checkout CTAs.',
                'route' => 'pricing',
                'url' => route('pricing'),
                'component' => 'pages/billing/pricing.tsx',
                'controller' => 'app/Http/Controllers/Billing/PricingController.php',
                'tags' => ['marketing', 'pricing', 'plans', 'checkout'],
                'guestOnly' => false,
                'color' => 'indigo',
            ],
            [
                'id' => 'help-center',
                'name' => 'Help Center',
                'category' => 'Marketing',
                'description' => 'Knowledge base index with article categories, search, and featured articles.',
                'route' => 'help.index',
                'url' => route('help.index'),
                'component' => 'pages/help-center/index.tsx',
                'controller' => 'modules/help/src/Http/Controllers/HelpCenterController.php',
                'tags' => ['help', 'knowledge-base', 'search', 'public'],
                'guestOnly' => false,
                'color' => 'indigo',
            ],

            // ─── Errors ─────────────────────────────────────────────────────
            [
                'id' => 'error-page',
                'name' => 'Error Page',
                'category' => 'Errors',
                'description' => 'Branded error page supporting 404, 500, 403, and 503 with friendly messages and action button.',
                'route' => null,
                'url' => null,
                'component' => 'pages/error.tsx',
                'controller' => null,
                'tags' => ['error', '404', '500', 'branded'],
                'guestOnly' => false,
                'color' => 'red',
            ],
        ];
    }
}
