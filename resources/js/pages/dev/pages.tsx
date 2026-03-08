import * as React from 'react';

import { Head, Link } from '@inertiajs/react';
import {
    ArrowUpRight,
    BookOpen,
    ChevronRight,
    Code2,
    Copy,
    ExternalLink,
    LayoutDashboard,
    Lock,
    Monitor,
    Puzzle,
    Search,
    Smartphone,
    Tablet,
    Tag,
    Terminal,
    X,
} from 'lucide-react';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Separator } from '@/components/ui/separator';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';

import { cn } from '@/lib/utils';

// ---------------------------------------------------------------------------
// Types
// ---------------------------------------------------------------------------

interface Template {
    id: string;
    name: string;
    category: string;
    description: string;
    route: string | null;
    url: string | null;
    component: string;
    controller: string | null;
    tags: string[];
    guestOnly: boolean;
    color: string;
}

interface Props {
    templates: Template[];
}

type Viewport = 'desktop' | 'tablet' | 'mobile';

// ---------------------------------------------------------------------------
// Category meta
// ---------------------------------------------------------------------------

const CATEGORY_ORDER = [
    'App',
    'Users',
    'Billing',
    'Settings',
    'Organizations',
    'Onboarding',
    'Auth',
    'Marketing',
    'Errors',
];

const CATEGORY_ICONS: Record<string, React.ReactNode> = {
    App: <LayoutDashboard className="size-3.5" />,
    Users: <Puzzle className="size-3.5" />,
    Billing: <Tag className="size-3.5" />,
    Settings: <Code2 className="size-3.5" />,
    Organizations: <BookOpen className="size-3.5" />,
    Onboarding: <ArrowUpRight className="size-3.5" />,
    Auth: <Lock className="size-3.5" />,
    Marketing: <ExternalLink className="size-3.5" />,
    Errors: <X className="size-3.5" />,
};

const COLOR_MAP: Record<string, string> = {
    blue: 'bg-blue-500/10 text-blue-600 dark:text-blue-400',
    green: 'bg-green-500/10 text-green-600 dark:text-green-400',
    purple: 'bg-purple-500/10 text-purple-600 dark:text-purple-400',
    amber: 'bg-amber-500/10 text-amber-600 dark:text-amber-400',
    gray: 'bg-gray-500/10 text-gray-600 dark:text-gray-400',
    cyan: 'bg-cyan-500/10 text-cyan-600 dark:text-cyan-400',
    emerald: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
    rose: 'bg-rose-500/10 text-rose-600 dark:text-rose-400',
    indigo: 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400',
    red: 'bg-red-500/10 text-red-600 dark:text-red-400',
};

const VIEWPORT_WIDTHS: Record<Viewport, number | null> = {
    desktop: null,
    tablet: 768,
    mobile: 390,
};

// ---------------------------------------------------------------------------
// Sidebar
// ---------------------------------------------------------------------------

function Sidebar({
    templates,
    search,
    onSearchChange,
    selectedCategory,
    onCategoryChange,
    selectedId,
    onSelect,
}: {
    templates: Template[];
    search: string;
    onSearchChange: (v: string) => void;
    selectedCategory: string;
    onCategoryChange: (v: string) => void;
    selectedId: string | null;
    onSelect: (t: Template) => void;
}) {
    const categories = React.useMemo(() => {
        const counts: Record<string, number> = {};
        templates.forEach((t) => {
            counts[t.category] = (counts[t.category] ?? 0) + 1;
        });
        return counts;
    }, [templates]);

    const filtered = React.useMemo(() => {
        return templates.filter((t) => {
            const matchesSearch =
                !search ||
                t.name.toLowerCase().includes(search.toLowerCase()) ||
                t.description.toLowerCase().includes(search.toLowerCase()) ||
                t.tags.some((tag) =>
                    tag.toLowerCase().includes(search.toLowerCase()),
                );
            const matchesCategory =
                selectedCategory === 'All' || t.category === selectedCategory;
            return matchesSearch && matchesCategory;
        });
    }, [templates, search, selectedCategory]);

    const grouped = React.useMemo(() => {
        const groups: Record<string, Template[]> = {};
        filtered.forEach((t) => {
            if (!groups[t.category]) groups[t.category] = [];
            groups[t.category].push(t);
        });
        return groups;
    }, [filtered]);

    const orderedGroups = CATEGORY_ORDER.filter((cat) => grouped[cat]);

    return (
        <aside className="flex w-64 shrink-0 flex-col border-r bg-muted/20">
            {/* Header */}
            <div className="border-b p-3">
                <div className="relative">
                    <Search className="absolute top-1/2 left-2.5 size-3.5 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        placeholder="Search templates…"
                        value={search}
                        onChange={(e) => onSearchChange(e.target.value)}
                        className="h-8 pl-8 text-sm"
                    />
                </div>
            </div>

            {/* Category pills */}
            <div className="border-b p-2">
                <button
                    onClick={() => onCategoryChange('All')}
                    className={cn(
                        'flex w-full items-center justify-between rounded-md px-2 py-1.5 text-sm transition-colors',
                        selectedCategory === 'All'
                            ? 'bg-primary text-primary-foreground'
                            : 'text-muted-foreground hover:bg-muted hover:text-foreground',
                    )}
                >
                    <span className="flex items-center gap-2">
                        <LayoutDashboard className="size-3.5" />
                        All templates
                    </span>
                    <span className="text-xs opacity-70">
                        {templates.length}
                    </span>
                </button>
                {CATEGORY_ORDER.map((cat) => (
                    <button
                        key={cat}
                        onClick={() => onCategoryChange(cat)}
                        className={cn(
                            'flex w-full items-center justify-between rounded-md px-2 py-1.5 text-sm transition-colors',
                            selectedCategory === cat
                                ? 'bg-primary text-primary-foreground'
                                : 'text-muted-foreground hover:bg-muted hover:text-foreground',
                            !categories[cat] && 'opacity-40',
                        )}
                        disabled={!categories[cat]}
                    >
                        <span className="flex items-center gap-2">
                            {CATEGORY_ICONS[cat] ?? (
                                <Tag className="size-3.5" />
                            )}
                            {cat}
                        </span>
                        {categories[cat] !== undefined && (
                            <span className="text-xs opacity-70">
                                {categories[cat]}
                            </span>
                        )}
                    </button>
                ))}
            </div>

            {/* Template list */}
            <div className="flex-1 overflow-auto">
                <div className="space-y-4 p-2">
                    {orderedGroups.length === 0 && (
                        <p className="px-2 py-4 text-center text-sm text-muted-foreground">
                            No templates found
                        </p>
                    )}
                    {orderedGroups.map((cat) => (
                        <div key={cat}>
                            <p className="mb-1 px-2 text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                {cat}
                            </p>
                            {grouped[cat].map((t) => (
                                <button
                                    key={t.id}
                                    onClick={() => onSelect(t)}
                                    className={cn(
                                        'flex w-full items-center gap-2 rounded-md px-2 py-2 text-left text-sm transition-colors',
                                        selectedId === t.id
                                            ? 'bg-primary text-primary-foreground'
                                            : 'text-foreground hover:bg-muted',
                                    )}
                                >
                                    <span
                                        className={cn(
                                            'flex size-6 shrink-0 items-center justify-center rounded',
                                            selectedId === t.id
                                                ? 'bg-white/20'
                                                : (COLOR_MAP[t.color] ??
                                                      COLOR_MAP.gray),
                                        )}
                                    >
                                        {CATEGORY_ICONS[t.category] ?? (
                                            <Tag className="size-3" />
                                        )}
                                    </span>
                                    <span className="truncate">{t.name}</span>
                                    {t.guestOnly && (
                                        <Lock className="ml-auto size-3 shrink-0 opacity-50" />
                                    )}
                                </button>
                            ))}
                        </div>
                    ))}
                </div>
            </div>
        </aside>
    );
}

// ---------------------------------------------------------------------------
// Template card (grid view)
// ---------------------------------------------------------------------------

function TemplateCard({
    template,
    onSelect,
}: {
    template: Template;
    onSelect: (t: Template) => void;
}) {
    return (
        <button
            onClick={() => onSelect(template)}
            className="group relative flex flex-col gap-3 rounded-xl border bg-card p-4 text-left transition-all hover:border-primary/50 hover:shadow-md"
        >
            {/* Color strip */}
            <div
                className={cn(
                    'flex h-28 w-full items-center justify-center rounded-lg',
                    COLOR_MAP[template.color] ?? COLOR_MAP.gray,
                )}
            >
                <span
                    className={cn(
                        'flex size-10 items-center justify-center rounded-lg bg-white/30 backdrop-blur-sm dark:bg-black/20',
                    )}
                >
                    {CATEGORY_ICONS[template.category] ?? (
                        <Tag className="size-5" />
                    )}
                </span>
            </div>

            {/* Meta */}
            <div className="flex-1 space-y-1.5">
                <div className="flex items-start justify-between gap-2">
                    <p className="leading-tight font-medium">{template.name}</p>
                    <ChevronRight className="mt-0.5 size-4 shrink-0 text-muted-foreground transition-transform group-hover:translate-x-0.5" />
                </div>
                <p className="line-clamp-2 text-xs leading-relaxed text-muted-foreground">
                    {template.description}
                </p>
            </div>

            {/* Footer */}
            <div className="flex flex-wrap items-center gap-1.5">
                <Badge variant="secondary" className="text-xs">
                    {template.category}
                </Badge>
                {template.guestOnly && (
                    <Badge variant="outline" className="text-xs">
                        <Lock className="mr-1 size-2.5" />
                        Guest only
                    </Badge>
                )}
            </div>
        </button>
    );
}

// ---------------------------------------------------------------------------
// Detail panel
// ---------------------------------------------------------------------------

function DetailPanel({
    template,
    onClose,
}: {
    template: Template;
    onClose: () => void;
}) {
    const [viewport, setViewport] = React.useState<Viewport>('desktop');
    const [copied, setCopied] = React.useState<string | null>(null);

    const iframeWidth = VIEWPORT_WIDTHS[viewport];
    const canPreview = template.url !== null && !template.guestOnly;

    function copy(text: string, key: string) {
        navigator.clipboard.writeText(text).catch(() => undefined);
        setCopied(key);
        setTimeout(() => setCopied(null), 2000);
    }

    const artisanCmd = `php artisan page:add ${template.component.replace('pages/', '').replace('.tsx', '')}`;
    const routeSnippet = template.route
        ? `Route::get('${template.component.replace('pages/', '').replace('.tsx', '')}', ${template.controller?.split('/').pop()?.replace('.php', '') ?? 'YourController'}::class)->name('${template.route}');`
        : null;

    return (
        <div className="flex flex-1 flex-col overflow-hidden">
            {/* Panel header */}
            <div className="flex items-center justify-between border-b px-4 py-3">
                <div className="flex items-center gap-3">
                    <span
                        className={cn(
                            'flex size-8 items-center justify-center rounded-lg text-sm',
                            COLOR_MAP[template.color] ?? COLOR_MAP.gray,
                        )}
                    >
                        {CATEGORY_ICONS[template.category] ?? (
                            <Tag className="size-4" />
                        )}
                    </span>
                    <div>
                        <h2 className="text-sm font-semibold">
                            {template.name}
                        </h2>
                        <p className="text-xs text-muted-foreground">
                            {template.category}
                        </p>
                    </div>
                </div>

                <div className="flex items-center gap-2">
                    {/* Viewport toggle */}
                    <div className="flex items-center rounded-lg border p-0.5">
                        <TooltipProvider>
                            {(
                                [
                                    [
                                        'desktop',
                                        <Monitor
                                            key="desktop"
                                            className="size-3.5"
                                        />,
                                        'Desktop (full width)',
                                    ],
                                    [
                                        'tablet',
                                        <Tablet
                                            key="tablet"
                                            className="size-3.5"
                                        />,
                                        'Tablet (768px)',
                                    ],
                                    [
                                        'mobile',
                                        <Smartphone
                                            key="mobile"
                                            className="size-3.5"
                                        />,
                                        'Mobile (390px)',
                                    ],
                                ] as [Viewport, React.ReactNode, string][]
                            ).map(([vp, icon, label]) => (
                                <Tooltip key={vp}>
                                    <TooltipTrigger asChild>
                                        <button
                                            onClick={() => setViewport(vp)}
                                            className={cn(
                                                'flex size-6 items-center justify-center rounded transition-colors',
                                                viewport === vp
                                                    ? 'bg-primary text-primary-foreground'
                                                    : 'text-muted-foreground hover:text-foreground',
                                            )}
                                        >
                                            {icon}
                                        </button>
                                    </TooltipTrigger>
                                    <TooltipContent>{label}</TooltipContent>
                                </Tooltip>
                            ))}
                        </TooltipProvider>
                    </div>

                    {template.url && (
                        <Button variant="outline" size="sm" asChild>
                            <a
                                href={template.url}
                                target="_blank"
                                rel="noreferrer"
                            >
                                <ExternalLink className="mr-1.5 size-3.5" />
                                Open
                            </a>
                        </Button>
                    )}

                    <Button
                        variant="ghost"
                        size="icon"
                        onClick={onClose}
                        className="size-7"
                    >
                        <X className="size-4" />
                    </Button>
                </div>
            </div>

            {/* Tabs */}
            <Tabs
                defaultValue="preview"
                className="flex flex-1 flex-col overflow-hidden"
            >
                <div className="border-b px-4">
                    <TabsList className="h-9 gap-0 bg-transparent p-0">
                        <TabsTrigger
                            value="preview"
                            className="rounded-none border-b-2 border-transparent px-3 text-sm data-[state=active]:border-primary data-[state=active]:bg-transparent data-[state=active]:shadow-none"
                        >
                            Preview
                        </TabsTrigger>
                        <TabsTrigger
                            value="code"
                            className="rounded-none border-b-2 border-transparent px-3 text-sm data-[state=active]:border-primary data-[state=active]:bg-transparent data-[state=active]:shadow-none"
                        >
                            Code
                        </TabsTrigger>
                        <TabsTrigger
                            value="info"
                            className="rounded-none border-b-2 border-transparent px-3 text-sm data-[state=active]:border-primary data-[state=active]:bg-transparent data-[state=active]:shadow-none"
                        >
                            Info
                        </TabsTrigger>
                    </TabsList>
                </div>

                {/* Preview tab */}
                <TabsContent
                    value="preview"
                    className="m-0 flex flex-1 flex-col overflow-hidden"
                >
                    {canPreview ? (
                        <div className="flex flex-1 flex-col items-center overflow-auto bg-muted/30 p-4">
                            <div
                                className="relative flex-1 overflow-hidden rounded-xl border bg-white shadow-lg transition-all duration-300"
                                style={{
                                    width: iframeWidth
                                        ? `${iframeWidth}px`
                                        : '100%',
                                    minHeight: '500px',
                                }}
                            >
                                <iframe
                                    src={template.url!}
                                    className="size-full border-0"
                                    title={template.name}
                                    style={{ height: '700px' }}
                                />
                            </div>
                            {iframeWidth && (
                                <p className="mt-2 text-xs text-muted-foreground">
                                    {iframeWidth}px — {viewport} viewport
                                </p>
                            )}
                        </div>
                    ) : (
                        <div className="flex flex-1 flex-col items-center justify-center gap-3 p-8 text-center">
                            <div className="flex size-12 items-center justify-center rounded-full bg-muted">
                                <Lock className="size-5 text-muted-foreground" />
                            </div>
                            <div>
                                <p className="font-medium">
                                    Preview unavailable
                                </p>
                                <p className="mt-1 text-sm text-muted-foreground">
                                    {template.guestOnly
                                        ? 'This page is for unauthenticated users only. Log out to preview it.'
                                        : 'No preview URL configured for this template.'}
                                </p>
                            </div>
                        </div>
                    )}
                </TabsContent>

                {/* Code tab */}
                <TabsContent
                    value="code"
                    className="m-0 flex flex-1 flex-col overflow-hidden"
                >
                    <div className="flex-1 overflow-auto">
                        <div className="space-y-4 p-4">
                            {/* Component path */}
                            <div>
                                <div className="mb-1.5 flex items-center justify-between">
                                    <p className="text-xs font-medium">
                                        React Component
                                    </p>
                                    <button
                                        onClick={() =>
                                            copy(
                                                `resources/js/${template.component}`,
                                                'component',
                                            )
                                        }
                                        className="flex items-center gap-1 rounded px-1.5 py-0.5 text-xs text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                                    >
                                        {copied === 'component' ? (
                                            <span className="text-green-500">
                                                Copied!
                                            </span>
                                        ) : (
                                            <Copy className="size-3" />
                                        )}
                                    </button>
                                </div>
                                <pre className="overflow-x-auto rounded-lg bg-zinc-950 p-3 text-xs text-zinc-100">
                                    <code>{`// resources/js/${template.component}`}</code>
                                </pre>
                            </div>

                            {/* Controller path */}
                            {template.controller && (
                                <div>
                                    <div className="mb-1.5 flex items-center justify-between">
                                        <p className="text-xs font-medium">
                                            Laravel Controller
                                        </p>
                                        <button
                                            onClick={() =>
                                                copy(
                                                    template.controller!,
                                                    'controller',
                                                )
                                            }
                                            className="flex items-center gap-1 rounded px-1.5 py-0.5 text-xs text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                                        >
                                            {copied === 'controller' ? (
                                                <span className="text-green-500">
                                                    Copied!
                                                </span>
                                            ) : (
                                                <Copy className="size-3" />
                                            )}
                                        </button>
                                    </div>
                                    <pre className="overflow-x-auto rounded-lg bg-zinc-950 p-3 text-xs text-zinc-100">
                                        <code>{`// ${template.controller}`}</code>
                                    </pre>
                                </div>
                            )}

                            {/* Route snippet */}
                            {routeSnippet && (
                                <div>
                                    <div className="mb-1.5 flex items-center justify-between">
                                        <p className="text-xs font-medium">
                                            Route (routes/web.php)
                                        </p>
                                        <button
                                            onClick={() =>
                                                copy(routeSnippet, 'route')
                                            }
                                            className="flex items-center gap-1 rounded px-1.5 py-0.5 text-xs text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                                        >
                                            {copied === 'route' ? (
                                                <span className="text-green-500">
                                                    Copied!
                                                </span>
                                            ) : (
                                                <Copy className="size-3" />
                                            )}
                                        </button>
                                    </div>
                                    <pre className="overflow-x-auto rounded-lg bg-zinc-950 p-3 text-xs text-zinc-100">
                                        <code>{routeSnippet}</code>
                                    </pre>
                                </div>
                            )}

                            {/* Artisan scaffold command */}
                            <div>
                                <div className="mb-1.5 flex items-center justify-between">
                                    <p className="text-xs font-medium">
                                        Scaffold a similar page
                                    </p>
                                    <button
                                        onClick={() =>
                                            copy(artisanCmd, 'artisan')
                                        }
                                        className="flex items-center gap-1 rounded px-1.5 py-0.5 text-xs text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                                    >
                                        {copied === 'artisan' ? (
                                            <span className="text-green-500">
                                                Copied!
                                            </span>
                                        ) : (
                                            <Copy className="size-3" />
                                        )}
                                    </button>
                                </div>
                                <pre className="overflow-x-auto rounded-lg bg-zinc-950 p-3 text-xs text-zinc-100">
                                    <code>
                                        <span className="text-zinc-400">
                                            ${' '}
                                        </span>
                                        {artisanCmd}
                                    </code>
                                </pre>
                                <p className="mt-1.5 text-xs text-muted-foreground">
                                    Generates a new page component + controller
                                    scaffolded from this template pattern.
                                </p>
                            </div>
                        </div>
                    </div>
                </TabsContent>

                {/* Info tab */}
                <TabsContent
                    value="info"
                    className="m-0 flex flex-1 flex-col overflow-hidden"
                >
                    <div className="flex-1 overflow-auto">
                        <div className="space-y-5 p-4">
                            {/* Description */}
                            <div>
                                <p className="mb-1 text-xs font-medium">
                                    Description
                                </p>
                                <p className="text-sm leading-relaxed text-muted-foreground">
                                    {template.description}
                                </p>
                            </div>

                            <Separator />

                            {/* Details */}
                            <div className="space-y-3">
                                <InfoRow
                                    label="Category"
                                    value={template.category}
                                />
                                <InfoRow
                                    label="Component"
                                    value={`resources/js/${template.component}`}
                                    mono
                                />
                                {template.controller && (
                                    <InfoRow
                                        label="Controller"
                                        value={template.controller}
                                        mono
                                    />
                                )}
                                {template.route && (
                                    <InfoRow
                                        label="Route name"
                                        value={template.route}
                                        mono
                                    />
                                )}
                                {template.url && (
                                    <InfoRow
                                        label="URL"
                                        value={template.url}
                                        mono
                                    />
                                )}
                                <InfoRow
                                    label="Auth required"
                                    value={
                                        template.guestOnly
                                            ? 'No (guest only)'
                                            : 'Yes'
                                    }
                                />
                            </div>

                            <Separator />

                            {/* Tags */}
                            <div>
                                <p className="mb-2 text-xs font-medium">Tags</p>
                                <div className="flex flex-wrap gap-1.5">
                                    {template.tags.map((tag) => (
                                        <Badge
                                            key={tag}
                                            variant="secondary"
                                            className="text-xs"
                                        >
                                            {tag}
                                        </Badge>
                                    ))}
                                </div>
                            </div>

                            <Separator />

                            {/* Actions */}
                            <div className="flex flex-col gap-2">
                                {template.url && (
                                    <Button variant="default" size="sm" asChild>
                                        <a
                                            href={template.url}
                                            target="_blank"
                                            rel="noreferrer"
                                        >
                                            <ExternalLink className="mr-1.5 size-3.5" />
                                            Open live page
                                        </a>
                                    </Button>
                                )}
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() =>
                                        copy(artisanCmd, 'artisan-info')
                                    }
                                >
                                    <Terminal className="mr-1.5 size-3.5" />
                                    {copied === 'artisan-info'
                                        ? 'Copied!'
                                        : 'Copy scaffold command'}
                                </Button>
                            </div>
                        </div>
                    </div>
                </TabsContent>
            </Tabs>
        </div>
    );
}

function InfoRow({
    label,
    value,
    mono = false,
}: {
    label: string;
    value: string;
    mono?: boolean;
}) {
    return (
        <div className="flex items-start justify-between gap-4">
            <p className="shrink-0 text-xs text-muted-foreground">{label}</p>
            <p
                className={cn(
                    'text-right text-xs',
                    mono && 'font-mono text-foreground',
                )}
            >
                {value}
            </p>
        </div>
    );
}

// ---------------------------------------------------------------------------
// Main page
// ---------------------------------------------------------------------------

export default function DevPages({ templates }: Props) {
    const [search, setSearch] = React.useState('');
    const [selectedCategory, setSelectedCategory] = React.useState('All');
    const [selected, setSelected] = React.useState<Template | null>(null);

    const filteredForGrid = React.useMemo(() => {
        return templates.filter((t) => {
            const matchesSearch =
                !search ||
                t.name.toLowerCase().includes(search.toLowerCase()) ||
                t.description.toLowerCase().includes(search.toLowerCase()) ||
                t.tags.some((tag) =>
                    tag.toLowerCase().includes(search.toLowerCase()),
                );
            const matchesCategory =
                selectedCategory === 'All' || t.category === selectedCategory;
            return matchesSearch && matchesCategory;
        });
    }, [templates, search, selectedCategory]);

    function handleSelect(t: Template) {
        setSelected((prev) => (prev?.id === t.id ? null : t));
    }

    return (
        <>
            <Head title="Page Gallery" />

            <div className="flex h-screen flex-col overflow-hidden">
                {/* Top bar */}
                <header className="flex h-12 shrink-0 items-center gap-4 border-b bg-background/95 px-4 backdrop-blur supports-[backdrop-filter]:bg-background/60">
                    <div className="flex items-center gap-2">
                        <Link
                            href="/dev/components"
                            className="flex items-center gap-1 text-sm text-muted-foreground transition-colors hover:text-foreground"
                        >
                            <Code2 className="size-4" />
                            <span>Components</span>
                        </Link>
                        <ChevronRight className="size-3.5 text-muted-foreground" />
                        <span className="text-sm font-medium">
                            Page Gallery
                        </span>
                    </div>

                    <div className="ml-auto flex items-center gap-3">
                        <p className="text-xs text-muted-foreground">
                            {templates.length} templates · Browse, preview, and
                            scaffold pages
                        </p>
                        <Badge variant="secondary" className="text-xs">
                            dev only
                        </Badge>
                    </div>
                </header>

                {/* Body */}
                <div className="flex flex-1 overflow-hidden">
                    {/* Sidebar */}
                    <Sidebar
                        templates={templates}
                        search={search}
                        onSearchChange={setSearch}
                        selectedCategory={selectedCategory}
                        onCategoryChange={setSelectedCategory}
                        selectedId={selected?.id ?? null}
                        onSelect={handleSelect}
                    />

                    {/* Main content */}
                    {selected ? (
                        <DetailPanel
                            template={selected}
                            onClose={() => setSelected(null)}
                        />
                    ) : (
                        <main className="flex-1 overflow-auto">
                            {/* Hero */}
                            <div className="border-b px-6 py-5">
                                <div className="flex items-center gap-3">
                                    <div className="flex size-10 items-center justify-center rounded-lg bg-primary/10">
                                        <LayoutDashboard className="size-5 text-primary" />
                                    </div>
                                    <div>
                                        <h1 className="text-xl font-semibold tracking-tight">
                                            Page Template Gallery
                                        </h1>
                                        <p className="text-sm text-muted-foreground">
                                            Browse all {templates.length} page
                                            templates with live previews, code,
                                            and scaffold commands.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {/* Grid */}
                            <div className="p-6">
                                {filteredForGrid.length === 0 ? (
                                    <div className="flex flex-col items-center justify-center py-16 text-center">
                                        <Search className="mb-3 size-8 text-muted-foreground" />
                                        <p className="font-medium">
                                            No templates found
                                        </p>
                                        <p className="mt-1 text-sm text-muted-foreground">
                                            Try a different search term or
                                            category.
                                        </p>
                                    </div>
                                ) : (
                                    <>
                                        {selectedCategory === 'All' ? (
                                            // Grouped by category
                                            CATEGORY_ORDER.filter((cat) =>
                                                filteredForGrid.some(
                                                    (t) => t.category === cat,
                                                ),
                                            ).map((cat) => (
                                                <div key={cat} className="mb-8">
                                                    <div className="mb-3 flex items-center gap-2">
                                                        <span className="text-muted-foreground">
                                                            {
                                                                CATEGORY_ICONS[
                                                                    cat
                                                                ]
                                                            }
                                                        </span>
                                                        <h2 className="text-sm font-medium">
                                                            {cat}
                                                        </h2>
                                                        <Separator className="flex-1" />
                                                        <span className="text-xs text-muted-foreground">
                                                            {
                                                                filteredForGrid.filter(
                                                                    (t) =>
                                                                        t.category ===
                                                                        cat,
                                                                ).length
                                                            }
                                                        </span>
                                                    </div>
                                                    <div className="grid grid-cols-2 gap-4 lg:grid-cols-3 xl:grid-cols-4">
                                                        {filteredForGrid
                                                            .filter(
                                                                (t) =>
                                                                    t.category ===
                                                                    cat,
                                                            )
                                                            .map((t) => (
                                                                <TemplateCard
                                                                    key={t.id}
                                                                    template={t}
                                                                    onSelect={
                                                                        handleSelect
                                                                    }
                                                                />
                                                            ))}
                                                    </div>
                                                </div>
                                            ))
                                        ) : (
                                            // Flat grid for single category
                                            <div className="grid grid-cols-2 gap-4 lg:grid-cols-3 xl:grid-cols-4">
                                                {filteredForGrid.map((t) => (
                                                    <TemplateCard
                                                        key={t.id}
                                                        template={t}
                                                        onSelect={handleSelect}
                                                    />
                                                ))}
                                            </div>
                                        )}
                                    </>
                                )}
                            </div>
                        </main>
                    )}
                </div>
            </div>
        </>
    );
}
