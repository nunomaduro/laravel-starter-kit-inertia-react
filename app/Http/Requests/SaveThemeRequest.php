<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SaveThemeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'dark' => ['required', 'string', 'in:navy,mirage,mint,black,cinder'],
            'primary' => ['required', 'string', 'in:indigo,blue,green,amber,purple,rose'],
            'light' => ['required', 'string', 'in:slate,gray,neutral'],
            'skin' => ['required', 'string', 'in:shadow,bordered,flat,elevated'],
            'radius' => ['required', 'string', 'in:none,sm,default,md,lg,full'],
            'layout' => ['sometimes', 'string', 'in:main,sideblock'],
            'font' => ['sometimes', 'string', 'in:ibm-plex-sans,inter,geist-sans,poppins,outfit,plus-jakarta-sans,instrument-sans'],
            'menuColor' => ['sometimes', 'string', 'in:default,primary,muted'],
            'menuAccent' => ['sometimes', 'string', 'in:subtle,strong,bordered'],
        ];
    }
}
