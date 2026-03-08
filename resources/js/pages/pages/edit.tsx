import PageController from '@/actions/App/Http/Controllers/PageController';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { lazy, Suspense } from 'react';

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { puckConfig } from '@/lib/puck-config';

const PuckEditor = lazy(() =>
    import('@measured/puck').then((m) => {
        void import('@measured/puck/puck.css');
        return { default: m.Puck };
    }),
);

interface PageRecord {
    id: number;
    name: string;
    slug: string;
    puck_json: Record<string, unknown>;
    is_published: boolean;
    meta_description?: string | null;
    meta_image?: string | null;
}

interface TemplateOption {
    key: string;
    label: string;
    data: { root: Record<string, unknown>; content: Record<string, unknown>[] };
}

interface Props {
    page: PageRecord | null;
    puckJson: {
        root: Record<string, unknown>;
        content: Record<string, unknown>[];
    };
    templates?: TemplateOption[];
}

const emptyPuckData: {
    root: Record<string, unknown>;
    content: Record<string, unknown>[];
} = { root: {}, content: [] };

export default function PageEdit({ page, puckJson, templates = [] }: Props) {
    const isCreate = page === null;
    const initialData = puckJson?.content ? puckJson : emptyPuckData;

    const { data, setData, post, put, processing, errors } = useForm({
        name: page?.name ?? '',
        slug: page?.slug ?? '',
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        puck_json: initialData as any,
        is_published: page?.is_published ?? false,
        meta_description: page?.meta_description ?? '',
        meta_image: page?.meta_image ?? '',
    });

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: dashboard().url },
        { title: 'Pages', href: PageController.index().url },
        {
            title: isCreate ? 'New page' : (page?.name ?? 'Edit'),
            href: isCreate
                ? PageController.create().url
                : PageController.edit.url({ page: page!.id }),
        },
    ];

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (isCreate) {
            post(PageController.store().url);
        } else {
            put(PageController.update.url({ page: page!.id }));
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={isCreate ? 'New page' : `Edit: ${page?.name ?? ''}`} />
            <form
                onSubmit={handleSubmit}
                className="flex h-full flex-1 flex-col gap-6 overflow-hidden p-4"
            >
                <div className="flex flex-wrap items-end gap-4 border-b pb-4">
                    <div className="grid gap-2">
                        <Label htmlFor="name">Name</Label>
                        <Input
                            id="name"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            placeholder="Page name"
                            className="w-64"
                        />
                        <InputError message={errors.name} />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="slug">Slug</Label>
                        <Input
                            id="slug"
                            value={data.slug}
                            onChange={(e) => setData('slug', e.target.value)}
                            placeholder="page-slug"
                            className="w-48 font-mono text-sm"
                        />
                        <InputError message={errors.slug} />
                    </div>
                    {isCreate && templates.length > 0 && (
                        <div className="grid gap-2">
                            <Label>Template</Label>
                            <Select
                                onValueChange={(key) => {
                                    const t = templates.find(
                                        (x) => x.key === key,
                                    );
                                    if (t) setData('puck_json', t.data);
                                }}
                            >
                                <SelectTrigger className="w-40">
                                    <SelectValue placeholder="Start from…" />
                                </SelectTrigger>
                                <SelectContent>
                                    {templates.map((t) => (
                                        <SelectItem key={t.key} value={t.key}>
                                            {t.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                    )}
                    {!isCreate && (
                        <div className="flex items-center gap-2">
                            <Switch
                                id="is_published"
                                checked={data.is_published}
                                onCheckedChange={(checked) =>
                                    setData('is_published', checked)
                                }
                            />
                            <Label htmlFor="is_published">Published</Label>
                        </div>
                    )}
                    <div className="flex gap-2">
                        <Button
                            type="submit"
                            disabled={processing}
                            data-pan={isCreate ? undefined : 'pages-edit-save'}
                        >
                            {isCreate ? 'Create' : 'Save'}
                        </Button>
                        {!isCreate && (
                            <>
                                <Button
                                    type="button"
                                    variant="outline"
                                    asChild
                                    data-pan="pages-edit-preview"
                                >
                                    <a
                                        href={PageController.preview.url({
                                            page: page!.id,
                                        })}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        Preview
                                    </a>
                                </Button>
                                <Button type="button" variant="outline" asChild>
                                    <a href={PageController.index().url}>
                                        Cancel
                                    </a>
                                </Button>
                            </>
                        )}
                    </div>
                </div>
                {!isCreate && (
                    <div className="grid gap-4 border-b pb-4 sm:grid-cols-2">
                        <div className="grid gap-2">
                            <Label htmlFor="meta_description">
                                Meta description (SEO)
                            </Label>
                            <Input
                                id="meta_description"
                                value={data.meta_description}
                                onChange={(e) =>
                                    setData('meta_description', e.target.value)
                                }
                                placeholder="Short description for search results"
                                className="max-w-md"
                                maxLength={500}
                            />
                            <InputError message={errors.meta_description} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="meta_image">
                                Meta image URL (OG)
                            </Label>
                            <Input
                                id="meta_image"
                                value={data.meta_image}
                                onChange={(e) =>
                                    setData('meta_image', e.target.value)
                                }
                                placeholder="https://…"
                                className="max-w-md"
                            />
                            <InputError message={errors.meta_image} />
                        </div>
                    </div>
                )}

                <div className="min-h-0 flex-1 rounded-lg border">
                    <Suspense
                        fallback={
                            <div className="flex h-96 items-center justify-center text-muted-foreground">
                                Loading editor…
                            </div>
                        }
                    >
                        <PuckEditor
                            config={puckConfig}
                            data={data.puck_json}
                            onChange={(next) => setData('puck_json', next)}
                            onPublish={(next) => setData('puck_json', next)}
                        />
                    </Suspense>
                </div>
            </form>
        </AppLayout>
    );
}
