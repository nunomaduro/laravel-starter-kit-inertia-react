<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

use App\Models\Organization;
use App\Services\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateBrandingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $organization = TenantContext::get();

        return $organization instanceof Organization && $this->user()?->canInOrganization('org.settings.manage', $organization);
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $presets = array_keys(config('theme.presets', []));
        $radii = array_keys(config('theme.radii', []));
        $fonts = array_keys(config('theme.fonts', []));

        return [
            'logo' => ['nullable', 'image', 'max:2048'],
            'theme_preset' => ['nullable', 'string', Rule::in($presets)],
            'theme_radius' => ['nullable', 'string', Rule::in($radii)],
            'theme_font' => ['nullable', 'string', Rule::in($fonts)],
            'allow_user_ui_customization' => ['nullable', 'boolean'],
        ];
    }
}
