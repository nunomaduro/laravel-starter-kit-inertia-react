<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Providers\SettingsOverlayServiceProvider;
use App\Settings\AiSettings;
use App\Settings\AppSettings;
use App\Settings\BillingSettings;
use App\Settings\MailSettings;
use App\Settings\PrismSettings;
use App\Settings\SetupWizardSettings;
use App\Settings\StripeSettings;
use App\Settings\TenancySettings;
use BackedEnum;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use UnitEnum;

final class SetupWizard extends Page implements HasForms
{
    use InteractsWithForms;

    /** @var array<string, mixed> */
    public ?array $data = [];

    protected static string|UnitEnum|null $navigationGroup = 'Settings · App';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRocketLaunch;

    protected static ?string $navigationLabel = 'Setup Wizard';

    protected static ?int $navigationSort = -1;

    protected string $view = 'filament.pages.setup-wizard';

    protected static ?string $title = 'Setup Wizard';

    protected static ?string $slug = 'setup-wizard';

    protected static bool $shouldRegisterNavigation = false;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return filament()->getCurrentPanel()?->getId() === 'system' && $user !== null && $user->isSuperAdmin();
    }

    public function mount(): void
    {
        $app = resolve(AppSettings::class);
        $mail = resolve(MailSettings::class);
        $billing = resolve(BillingSettings::class);
        $stripe = resolve(StripeSettings::class);
        $prism = resolve(PrismSettings::class);
        $ai = resolve(AiSettings::class);

        $tenancy = resolve(TenancySettings::class);

        $this->form->fill([
            // Step 1: App Basics
            'site_name' => $app->site_name,
            'url' => $app->url,
            'timezone' => $app->timezone,
            'locale' => $app->locale,
            'fallback_locale' => $app->fallback_locale,

            // Step 2: Mail
            'mailer' => $mail->mailer,
            'smtp_host' => $mail->smtp_host,
            'smtp_port' => $mail->smtp_port,
            'smtp_username' => $mail->smtp_username,
            'smtp_password' => $mail->smtp_password,
            'smtp_encryption' => $mail->smtp_encryption,
            'from_address' => $mail->from_address,
            'from_name' => $mail->from_name,

            // Step 2b: Tenancy
            'tenancy_enabled' => $tenancy->enabled,
            'tenancy_term' => $tenancy->term,
            'tenancy_term_plural' => $tenancy->term_plural,
            'tenancy_allow_user_org_creation' => $tenancy->allow_user_org_creation,
            'tenancy_default_org_name' => $tenancy->default_org_name,
            'tenancy_auto_create_personal_org_for_admins' => $tenancy->auto_create_personal_org_for_admins,
            'tenancy_auto_create_personal_org_for_members' => $tenancy->auto_create_personal_org_for_members,
            'tenancy_invitation_expires_in_days' => $tenancy->invitation_expires_in_days,

            // Step 3: Billing
            'default_gateway' => $billing->default_gateway,
            'currency' => $billing->currency,
            'trial_days' => $billing->trial_days,
            'stripe_key' => $stripe->key,
            'stripe_secret' => $stripe->secret,
            'stripe_webhook_secret' => $stripe->webhook_secret,

            // Step 4: AI
            'prism_default_provider' => $prism->default_provider,
            'prism_default_model' => $prism->default_model,
            'prism_openai_api_key' => $prism->openai_api_key,
            'prism_anthropic_api_key' => $prism->anthropic_api_key,
            'prism_groq_api_key' => $prism->groq_api_key,
            'prism_xai_api_key' => $prism->xai_api_key,
            'prism_gemini_api_key' => $prism->gemini_api_key,
            'prism_deepseek_api_key' => $prism->deepseek_api_key,
            'prism_mistral_api_key' => $prism->mistral_api_key,
            'prism_openrouter_api_key' => $prism->openrouter_api_key,
            'ai_default_provider' => $ai->default_provider,
            'ai_cohere_api_key' => $ai->cohere_api_key,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Wizard::make([
                    $this->appBasicsStep(),
                    $this->tenancyStep(),
                    $this->mailStep(),
                    $this->billingStep(),
                    $this->aiStep(),
                    $this->completeStep(),
                ])
                    ->persistStepInQueryString()
                    ->submitAction(new HtmlString(Blade::render(<<<'BLADE'
                        <x-filament::button type="submit" size="sm">
                            Complete Setup
                        </x-filament::button>
                    BLADE))),
            ])
            ->statePath('data');
    }

    public function completeSetup(): void
    {
        $data = $this->form->getState();

        $this->saveAppSettings($data);
        $this->saveTenancySettings($data);
        $this->saveMailSettings($data);
        $this->saveBillingSettings($data);
        $this->saveAiSettings($data);

        $wizard = resolve(SetupWizardSettings::class);
        $wizard->setup_completed = true;
        $wizard->completed_steps = ['app', 'mail', 'billing', 'ai', 'complete'];
        $wizard->save();

        SettingsOverlayServiceProvider::applyOverlay();

        Notification::make()
            ->title('Setup complete!')
            ->body('Your application has been configured successfully.')
            ->success()
            ->send();

        $this->redirect(filament()->getUrl());
    }

    private function appBasicsStep(): Step
    {
        return Step::make('App Basics')
            ->description('Configure your application identity')
            ->schema([
                TextInput::make('site_name')
                    ->label('Site name')
                    ->required(),
                TextInput::make('url')
                    ->label('Application URL')
                    ->url()
                    ->required()
                    ->placeholder('https://example.com')
                    ->helperText('Full public URL used for links in emails, webhooks, and OAuth callbacks.'),
                TextInput::make('timezone')
                    ->label('Timezone')
                    ->required(),
                Select::make('locale')
                    ->label('Locale')
                    ->options([
                        'en' => 'English',
                        'es' => 'Spanish',
                        'fr' => 'French',
                        'de' => 'German',
                        'pt' => 'Portuguese',
                        'it' => 'Italian',
                        'nl' => 'Dutch',
                        'ja' => 'Japanese',
                        'ko' => 'Korean',
                        'zh' => 'Chinese',
                        'ar' => 'Arabic',
                    ])
                    ->required()
                    ->searchable(),
                Select::make('fallback_locale')
                    ->label('Fallback locale')
                    ->options([
                        'en' => 'English',
                        'es' => 'Spanish',
                        'fr' => 'French',
                        'de' => 'German',
                        'pt' => 'Portuguese',
                    ])
                    ->required()
                    ->searchable(),
            ])
            ->afterValidation(function (): void {
                $this->saveAppSettings($this->form->getState());
            })
            ->columns(2);
    }

    private function tenancyStep(): Step
    {
        return Step::make('Tenancy')
            ->description('Configure multi-tenancy and organization settings')
            ->schema([
                Section::make('Operating Mode')
                    ->schema([
                        \Filament\Forms\Components\Toggle::make('tenancy_enabled')
                            ->label('Enable multi-tenancy')
                            ->helperText('When enabled, users belong to organizations. Disable for single-tenant / internal-tool deployments.')
                            ->live(),
                        TextInput::make('tenancy_term')
                            ->label('Organization term (singular)')
                            ->placeholder('Organization')
                            ->helperText('e.g. "Workspace", "Team", "Company"'),
                        TextInput::make('tenancy_term_plural')
                            ->label('Organization term (plural)')
                            ->placeholder('Organizations'),
                    ])
                    ->columns(2),
                Section::make('Organization Defaults')
                    ->schema([
                        TextInput::make('tenancy_default_org_name')
                            ->label('Default organization name pattern')
                            ->helperText('Use {name} as placeholder for the user\'s name. e.g. "{name}\'s Workspace"'),
                        TextInput::make('tenancy_invitation_expires_in_days')
                            ->label('Invitation expires in (days)')
                            ->numeric(),
                        \Filament\Forms\Components\Toggle::make('tenancy_allow_user_org_creation')
                            ->label('Allow users to create organizations'),
                        \Filament\Forms\Components\Toggle::make('tenancy_auto_create_personal_org_for_admins')
                            ->label('Auto-create personal workspace (for org admins)')
                            ->helperText('Users who register or are added as admins get a personal org.'),
                        \Filament\Forms\Components\Toggle::make('tenancy_auto_create_personal_org_for_members')
                            ->label('Auto-create personal workspace (for org members)')
                            ->helperText('Users who join only as members (e.g. via invite) get a personal org.'),
                    ])
                    ->columns(2),
            ])
            ->afterValidation(function (): void {
                $this->saveTenancySettings($this->form->getState());
            });
    }

    private function mailStep(): Step
    {
        return Step::make('Mail')
            ->description('Configure email delivery')
            ->schema([
                Section::make('Mailer')
                    ->schema([
                        Select::make('mailer')
                            ->label('Mailer')
                            ->options([
                                'smtp' => 'SMTP',
                                'ses' => 'SES',
                                'postmark' => 'Postmark',
                                'resend' => 'Resend',
                                'sendmail' => 'Sendmail',
                                'log' => 'Log',
                            ])
                            ->required()
                            ->live(),
                    ]),
                Section::make('SMTP Configuration')
                    ->visible(fn (Get $get): bool => $get('mailer') === 'smtp')
                    ->schema([
                        TextInput::make('smtp_host')
                            ->label('SMTP host'),
                        TextInput::make('smtp_port')
                            ->label('SMTP port')
                            ->numeric(),
                        TextInput::make('smtp_username')
                            ->label('SMTP username'),
                        TextInput::make('smtp_password')
                            ->label('SMTP password')
                            ->password()
                            ->revealable(),
                        Select::make('smtp_encryption')
                            ->label('SMTP encryption')
                            ->options([
                                '' => 'None',
                                'tls' => 'TLS',
                                'ssl' => 'SSL',
                            ]),
                    ])
                    ->columns(2),
                Section::make('From Address')
                    ->schema([
                        TextInput::make('from_address')
                            ->label('From address')
                            ->email(),
                        TextInput::make('from_name')
                            ->label('From name'),
                    ])
                    ->columns(2),
            ])
            ->afterValidation(function (): void {
                $this->saveMailSettings($this->form->getState());
            });
    }

    private function billingStep(): Step
    {
        return Step::make('Billing')
            ->description('Configure payment processing (optional)')
            ->schema([
                Section::make('General')
                    ->schema([
                        Select::make('default_gateway')
                            ->label('Default gateway')
                            ->options([
                                'stripe' => 'Stripe',
                                'paddle' => 'Paddle',
                                'manual' => 'Manual',
                            ])
                            ->required(),
                        TextInput::make('currency')
                            ->label('Currency code')
                            ->required(),
                        TextInput::make('trial_days')
                            ->label('Trial days')
                            ->numeric()
                            ->required(),
                    ])
                    ->columns(3),
                Section::make('Stripe Keys')
                    ->schema([
                        TextInput::make('stripe_key')
                            ->label('Publishable key')
                            ->password()
                            ->revealable(),
                        TextInput::make('stripe_secret')
                            ->label('Secret key')
                            ->password()
                            ->revealable(),
                        TextInput::make('stripe_webhook_secret')
                            ->label('Webhook secret')
                            ->password()
                            ->revealable(),
                    ]),
            ])
            ->afterValidation(function (): void {
                $this->saveBillingSettings($this->form->getState());
            });
    }

    private function aiStep(): Step
    {
        return Step::make('AI')
            ->description('Configure AI providers (optional)')
            ->schema([
                Section::make('Prism (LLM Gateway)')
                    ->schema([
                        Select::make('prism_default_provider')
                            ->label('Default provider')
                            ->options([
                                'openrouter' => 'OpenRouter',
                                'openai' => 'OpenAI',
                                'anthropic' => 'Anthropic',
                                'ollama' => 'Ollama',
                                'mistral' => 'Mistral',
                                'groq' => 'Groq',
                                'xai' => 'xAI',
                                'gemini' => 'Gemini',
                                'deepseek' => 'DeepSeek',
                            ])
                            ->required()
                            ->live(),
                        TextInput::make('prism_default_model')
                            ->label('Default model'),
                        TextInput::make('prism_openrouter_api_key')
                            ->label('OpenRouter API key')
                            ->password()
                            ->revealable()
                            ->visible(fn (Get $get): bool => $get('prism_default_provider') === 'openrouter'),
                        TextInput::make('prism_openai_api_key')
                            ->label('OpenAI API key')
                            ->password()
                            ->revealable()
                            ->visible(fn (Get $get): bool => $get('prism_default_provider') === 'openai'),
                        TextInput::make('prism_anthropic_api_key')
                            ->label('Anthropic API key')
                            ->password()
                            ->revealable()
                            ->visible(fn (Get $get): bool => $get('prism_default_provider') === 'anthropic'),
                        TextInput::make('prism_groq_api_key')
                            ->label('Groq API key')
                            ->password()
                            ->revealable()
                            ->visible(fn (Get $get): bool => $get('prism_default_provider') === 'groq'),
                        TextInput::make('prism_xai_api_key')
                            ->label('xAI API key')
                            ->password()
                            ->revealable()
                            ->visible(fn (Get $get): bool => $get('prism_default_provider') === 'xai'),
                        TextInput::make('prism_gemini_api_key')
                            ->label('Gemini API key')
                            ->password()
                            ->revealable()
                            ->visible(fn (Get $get): bool => $get('prism_default_provider') === 'gemini'),
                        TextInput::make('prism_deepseek_api_key')
                            ->label('DeepSeek API key')
                            ->password()
                            ->revealable()
                            ->visible(fn (Get $get): bool => $get('prism_default_provider') === 'deepseek'),
                        TextInput::make('prism_mistral_api_key')
                            ->label('Mistral API key')
                            ->password()
                            ->revealable()
                            ->visible(fn (Get $get): bool => $get('prism_default_provider') === 'mistral'),
                        Placeholder::make('prism_ollama_placeholder')
                            ->label('Ollama')
                            ->content('Ollama runs locally and does not require an API key.')
                            ->visible(fn (Get $get): bool => $get('prism_default_provider') === 'ollama'),
                    ]),
                Section::make('Laravel AI SDK')
                    ->schema([
                        Select::make('ai_default_provider')
                            ->label('Default provider')
                            ->options([
                                'openai' => 'OpenAI',
                                'openrouter' => 'OpenRouter',
                                'anthropic' => 'Anthropic',
                                'gemini' => 'Gemini',
                                'groq' => 'Groq',
                                'xai' => 'xAI',
                                'cohere' => 'Cohere',
                            ])
                            ->required()
                            ->live(),
                        TextInput::make('ai_cohere_api_key')
                            ->label('Cohere API key')
                            ->password()
                            ->revealable()
                            ->visible(fn (Get $get): bool => $get('ai_default_provider') === 'cohere'),
                        Placeholder::make('ai_shared_prism_placeholder')
                            ->content("This provider's API key is shared with the Prism configuration above.")
                            ->visible(fn (Get $get): bool => $get('ai_default_provider') !== 'cohere'),
                    ]),
            ])
            ->afterValidation(function (): void {
                $this->saveAiSettings($this->form->getState());
            });
    }

    private function completeStep(): Step
    {
        return Step::make('Complete')
            ->description('Review and finalize')
            ->schema([
                Section::make('Ready to go!')
                    ->description('Click "Complete Setup" to save your configuration and start using the application. You can reconfigure these settings anytime from the Settings menu.')
                    ->schema([]),
            ]);
    }

    /** @param array<string, mixed> $data */
    private function saveAppSettings(array $data): void
    {
        $settings = resolve(AppSettings::class);
        $settings->site_name = $data['site_name'];
        $settings->url = $data['url'];
        $settings->timezone = $data['timezone'];
        $settings->locale = $data['locale'];
        $settings->fallback_locale = $data['fallback_locale'];
        $settings->save();
    }

    /** @param array<string, mixed> $data */
    private function saveTenancySettings(array $data): void
    {
        $settings = resolve(TenancySettings::class);
        $settings->enabled = (bool) ($data['tenancy_enabled'] ?? true);
        $settings->term = $data['tenancy_term'] ?: 'Organization';
        $settings->term_plural = $data['tenancy_term_plural'] ?: 'Organizations';
        $settings->allow_user_org_creation = (bool) ($data['tenancy_allow_user_org_creation'] ?? true);
        $settings->default_org_name = $data['tenancy_default_org_name'] ?: "{name}'s Workspace";
        $settings->auto_create_personal_org_for_admins = (bool) ($data['tenancy_auto_create_personal_org_for_admins'] ?? true);
        $settings->auto_create_personal_org_for_members = (bool) ($data['tenancy_auto_create_personal_org_for_members'] ?? false);
        $settings->auto_create_personal_org = $settings->auto_create_personal_org_for_admins;
        $settings->invitation_expires_in_days = (int) ($data['tenancy_invitation_expires_in_days'] ?? 7);
        $settings->save();
    }

    /** @param array<string, mixed> $data */
    private function saveMailSettings(array $data): void
    {
        $settings = resolve(MailSettings::class);
        $settings->mailer = $data['mailer'];
        $settings->smtp_host = $data['smtp_host'] ?? '127.0.0.1';
        $settings->smtp_port = (int) ($data['smtp_port'] ?? 2525);
        $settings->smtp_username = $data['smtp_username'];
        $settings->smtp_password = $data['smtp_password'];
        $settings->smtp_encryption = $data['smtp_encryption'];
        $settings->from_address = $data['from_address'] ?? 'hello@example.com';
        $settings->from_name = $data['from_name'] ?? 'Example';
        $settings->save();
    }

    /** @param array<string, mixed> $data */
    private function saveBillingSettings(array $data): void
    {
        $billing = resolve(BillingSettings::class);
        $billing->default_gateway = $data['default_gateway'];
        $billing->currency = $data['currency'];
        $billing->trial_days = (int) $data['trial_days'];
        $billing->save();

        $stripe = resolve(StripeSettings::class);
        $stripe->key = $data['stripe_key'];
        $stripe->secret = $data['stripe_secret'];
        $stripe->webhook_secret = $data['stripe_webhook_secret'];
        $stripe->save();
    }

    /** @param array<string, mixed> $data */
    private function saveAiSettings(array $data): void
    {
        $prism = resolve(PrismSettings::class);
        $prism->default_provider = $data['prism_default_provider'];
        $prism->default_model = $data['prism_default_model'] ?? 'deepseek/deepseek-r1-0528:free';
        $prism->openrouter_api_key = $data['prism_openrouter_api_key'] ?? null;
        $prism->openai_api_key = $data['prism_openai_api_key'] ?? null;
        $prism->anthropic_api_key = $data['prism_anthropic_api_key'] ?? null;
        $prism->groq_api_key = $data['prism_groq_api_key'] ?? null;
        $prism->xai_api_key = $data['prism_xai_api_key'] ?? null;
        $prism->gemini_api_key = $data['prism_gemini_api_key'] ?? null;
        $prism->deepseek_api_key = $data['prism_deepseek_api_key'] ?? null;
        $prism->mistral_api_key = $data['prism_mistral_api_key'] ?? null;
        $prism->save();

        $ai = resolve(AiSettings::class);
        $ai->default_provider = $data['ai_default_provider'];
        $ai->cohere_api_key = $data['ai_cohere_api_key'] ?? null;
        $ai->save();
    }
}
