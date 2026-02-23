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
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
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

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?string $navigationLabel = 'Setup Wizard';

    protected static ?int $navigationSort = -1;

    protected string $view = 'filament.pages.setup-wizard';

    protected static ?string $title = 'Setup Wizard';

    protected static ?string $slug = 'setup-wizard';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user !== null && $user->isSuperAdmin();
    }

    public function mount(): void
    {
        $app = resolve(AppSettings::class);
        $mail = resolve(MailSettings::class);
        $billing = resolve(BillingSettings::class);
        $stripe = resolve(StripeSettings::class);
        $prism = resolve(PrismSettings::class);
        $ai = resolve(AiSettings::class);

        $this->form->fill([
            // Step 1: App Basics
            'site_name' => $app->site_name,
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
            'openrouter_api_key' => $prism->openrouter_api_key,
            'ai_default_provider' => $ai->default_provider,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Wizard::make([
                    $this->appBasicsStep(),
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
                            ->required(),
                    ]),
                Section::make('SMTP Configuration')
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
                            ->required(),
                        TextInput::make('prism_default_model')
                            ->label('Default model'),
                        TextInput::make('openrouter_api_key')
                            ->label('OpenRouter API key')
                            ->password()
                            ->revealable(),
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
                            ->required(),
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
        $settings->timezone = $data['timezone'];
        $settings->locale = $data['locale'];
        $settings->fallback_locale = $data['fallback_locale'];
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
        $prism->openrouter_api_key = $data['openrouter_api_key'];
        $prism->save();

        $ai = resolve(AiSettings::class);
        $ai->default_provider = $data['ai_default_provider'];
        $ai->save();
    }
}
