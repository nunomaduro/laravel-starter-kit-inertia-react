import { Head, useForm } from '@inertiajs/react';

import HeadingSmall from '@/components/heading-small';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import {
    edit as editBranding,
    update as updateBranding,
} from '@/routes/settings/branding';
import { type BreadcrumbItem } from '@/types';

interface BrandingProps {
    logoUrl: string | null;
    themePreset: string | null;
    themeRadius: string | null;
    themeFont: string | null;
    allowUserCustomization: boolean;
}

interface Props {
    branding: BrandingProps;
    presetOptions: Record<string, string>;
    radiusOptions: Record<string, string>;
    fontOptions: Record<string, string>;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Organization branding', href: editBranding().url },
];

export default function Branding({
    branding,
    presetOptions,
    radiusOptions,
    fontOptions,
}: Props) {
    const { data, setData, put, processing, errors } = useForm({
        logo: null as File | null,
        theme_preset: branding.themePreset ?? '',
        theme_radius: branding.themeRadius ?? '',
        theme_font: branding.themeFont ?? '',
        allow_user_ui_customization: branding.allowUserCustomization,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(updateBranding().url, {
            forceFormData: true,
            preserveScroll: true,
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Organization branding" />

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall
                        title="Organization branding"
                        description="Logo and theme overrides for your organization"
                    />

                    <form
                        onSubmit={handleSubmit}
                        encType="multipart/form-data"
                        className="space-y-6"
                    >
                        <div className="space-y-2">
                            <Label htmlFor="logo">Logo</Label>
                            {branding.logoUrl && (
                                <div className="mb-2 flex items-center gap-4">
                                    <img
                                        src={branding.logoUrl}
                                        alt="Current logo"
                                        className="h-16 w-auto object-contain"
                                    />
                                </div>
                            )}
                            <input
                                id="logo"
                                name="logo"
                                type="file"
                                accept="image/*"
                                className="block w-full max-w-xs text-sm text-muted-foreground file:mr-4 file:rounded-md file:border-0 file:bg-primary file:px-4 file:py-2 file:text-sm file:font-medium file:text-primary-foreground hover:file:bg-primary/90"
                                onChange={(e) =>
                                    setData('logo', e.target.files?.[0] ?? null)
                                }
                            />
                            <p className="text-xs text-muted-foreground">
                                Image file. Max 2 MB. Leave empty to keep
                                current.
                            </p>
                            <InputError message={errors.logo} />
                        </div>

                        <div className="space-y-2">
                            <Label>Theme preset</Label>
                            <Select
                                value={data.theme_preset || undefined}
                                onValueChange={(v) =>
                                    setData('theme_preset', v ?? '')
                                }
                            >
                                <SelectTrigger className="w-full max-w-xs">
                                    <SelectValue placeholder="Use app default" />
                                </SelectTrigger>
                                <SelectContent>
                                    {Object.entries(presetOptions).map(
                                        ([value, label]) => (
                                            <SelectItem
                                                key={value}
                                                value={value}
                                            >
                                                {label}
                                            </SelectItem>
                                        ),
                                    )}
                                </SelectContent>
                            </Select>
                            <InputError message={errors.theme_preset} />
                        </div>

                        <div className="space-y-2">
                            <Label>Radius</Label>
                            <Select
                                value={data.theme_radius || undefined}
                                onValueChange={(v) =>
                                    setData('theme_radius', v ?? '')
                                }
                            >
                                <SelectTrigger className="w-full max-w-xs">
                                    <SelectValue placeholder="Use app default" />
                                </SelectTrigger>
                                <SelectContent>
                                    {Object.entries(radiusOptions).map(
                                        ([value, label]) => (
                                            <SelectItem
                                                key={value}
                                                value={value}
                                            >
                                                {label}
                                            </SelectItem>
                                        ),
                                    )}
                                </SelectContent>
                            </Select>
                            <InputError message={errors.theme_radius} />
                        </div>

                        <div className="space-y-2">
                            <Label>Font</Label>
                            <Select
                                value={data.theme_font || undefined}
                                onValueChange={(v) =>
                                    setData('theme_font', v ?? '')
                                }
                            >
                                <SelectTrigger className="w-full max-w-xs">
                                    <SelectValue placeholder="Use app default" />
                                </SelectTrigger>
                                <SelectContent>
                                    {Object.entries(fontOptions).map(
                                        ([value, label]) => (
                                            <SelectItem
                                                key={value}
                                                value={value}
                                            >
                                                {label}
                                            </SelectItem>
                                        ),
                                    )}
                                </SelectContent>
                            </Select>
                            <InputError message={errors.theme_font} />
                        </div>

                        <div className="flex items-center space-x-2">
                            <Switch
                                id="allow_user_ui_customization"
                                checked={data.allow_user_ui_customization}
                                onCheckedChange={(checked) =>
                                    setData(
                                        'allow_user_ui_customization',
                                        checked,
                                    )
                                }
                            />
                            <Label
                                htmlFor="allow_user_ui_customization"
                                className="font-normal"
                            >
                                Allow members to change appearance (light/dark)
                            </Label>
                        </div>
                        <InputError
                            message={errors.allow_user_ui_customization}
                        />

                        <Button type="submit" disabled={processing}>
                            {processing ? 'Saving…' : 'Save branding'}
                        </Button>
                    </form>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
