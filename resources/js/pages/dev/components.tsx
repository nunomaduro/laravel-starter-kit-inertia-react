import * as React from 'react';

import { Head, Link } from '@inertiajs/react';
import {
    AlertTriangle,
    BarChart3,
    Bell,
    Check,
    Code2,
    Copy,
    FileText,
    Globe,
    Layers,
    LayoutGrid,
    Map,
    Palette,
    Puzzle,
    Shield,
    Sliders,
    Sparkles,
    Star,
    Users,
    Zap,
} from 'lucide-react';

import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { ConfirmDialog } from '@/components/ui/confirm-dialog';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { KeyboardShortcutDisplay } from '@/components/ui/keyboard-shortcut-display';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { Progress } from '@/components/ui/progress';
import { Separator } from '@/components/ui/separator';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { Skeleton } from '@/components/ui/skeleton';
import { Spinner } from '@/components/ui/spinner';
import { Switch } from '@/components/ui/switch';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { ThemeCustomizerPanel } from '@/components/ui/theme-customizer';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';

import { AreaChart } from '@/components/charts/area-chart';
import { BarChart } from '@/components/charts/bar-chart';
import { GaugeChart } from '@/components/charts/gauge-chart';
import { PieChart } from '@/components/charts/pie-chart';
import { ProgressRing } from '@/components/charts/progress-ring';
import { Sparkline } from '@/components/charts/sparkline';

import { ConfidenceScore } from '@/components/ai/confidence-score';
import { StreamingText } from '@/components/ai/streaming-text';
import { ThinkingIndicator } from '@/components/ai/thinking-indicator';
import { TokenUsageDisplay } from '@/components/ai/token-usage';

import { FeatureGate } from '@/components/saas/feature-gate';
import { TrialBanner } from '@/components/saas/trial-banner';
import { UsageMeter } from '@/components/saas/usage-meter';

import { AuditLogViewer } from '@/components/admin/audit-log-viewer';
import { PermissionMatrix } from '@/components/admin/permission-matrix';

import { ActivityLog } from '@/components/composed/activity-log';
import { MetricDashboard } from '@/components/composed/metric-dashboard';
import { PricingCard } from '@/components/composed/pricing-card';
import { UserCard } from '@/components/composed/user-card';

import { AppShell } from '@/components/shells/app-shell';

import { DARK_THEMES, PRIMARY_COLORS } from '@/lib/tailux-themes';
import { cn } from '@/lib/utils';

// ---------------------------------------------------------------------------
// Category definitions
// ---------------------------------------------------------------------------

interface Category {
    id: string;
    label: string;
    icon: React.ReactNode;
}

const CATEGORIES: Category[] = [
    {
        id: 'foundation',
        label: 'Foundation',
        icon: <Palette className="size-4" />,
    },
    { id: 'layout', label: 'Layout', icon: <LayoutGrid className="size-4" /> },
    { id: 'shells', label: 'Shells', icon: <Layers className="size-4" /> },
    {
        id: 'navigation',
        label: 'Navigation',
        icon: <Globe className="size-4" />,
    },
    {
        id: 'buttons',
        label: 'Buttons & Actions',
        icon: <Zap className="size-4" />,
    },
    { id: 'forms', label: 'Forms', icon: <Sliders className="size-4" /> },
    {
        id: 'data-display',
        label: 'Data Display',
        icon: <FileText className="size-4" />,
    },
    { id: 'feedback', label: 'Feedback', icon: <Bell className="size-4" /> },
    { id: 'overlay', label: 'Overlay', icon: <Layers className="size-4" /> },
    { id: 'charts', label: 'Charts', icon: <BarChart3 className="size-4" /> },
    { id: 'maps', label: 'Maps', icon: <Map className="size-4" /> },
    { id: 'ai', label: 'AI', icon: <Sparkles className="size-4" /> },
    { id: 'saas', label: 'SaaS', icon: <Star className="size-4" /> },
    { id: 'admin', label: 'Admin', icon: <Shield className="size-4" /> },
    { id: 'composed', label: 'Composed', icon: <Puzzle className="size-4" /> },
    {
        id: 'accessibility',
        label: 'Accessibility',
        icon: <Users className="size-4" />,
    },
];

// ---------------------------------------------------------------------------
// Active theme hook (reads DOM attrs, updates on mutation)
// ---------------------------------------------------------------------------

interface ActiveTheme {
    dark: string;
    primary: string;
    radius: string;
    skin: string;
}

function getThemeFromDOM(): ActiveTheme {
    if (typeof document === 'undefined') {
        return {
            dark: 'navy',
            primary: 'indigo',
            radius: 'default',
            skin: 'shadow',
        };
    }
    const el = document.documentElement;
    return {
        dark: el.getAttribute('data-theme-dark') ?? 'navy',
        primary: el.getAttribute('data-theme-primary') ?? 'indigo',
        radius: el.getAttribute('data-radius') ?? 'default',
        skin: el.getAttribute('data-card-skin') ?? 'shadow',
    };
}

function useActiveTheme(): ActiveTheme {
    const [theme, setTheme] = React.useState<ActiveTheme>(getThemeFromDOM);

    React.useEffect(() => {
        const observer = new MutationObserver(() =>
            setTheme(getThemeFromDOM()),
        );
        observer.observe(document.documentElement, { attributes: true });
        return () => observer.disconnect();
    }, []);

    return theme;
}

// ---------------------------------------------------------------------------
// Layout helpers
// ---------------------------------------------------------------------------

function ShowcaseSection({
    id,
    title,
    description,
    badge,
    children,
}: {
    id: string;
    title: string;
    description?: string;
    badge?: string;
    children: React.ReactNode;
}) {
    return (
        <section id={id} className="scroll-mt-20 space-y-6">
            <div className="border-b pb-3">
                <div className="flex items-center gap-3">
                    <h2 className="text-xl font-semibold tracking-tight">
                        {title}
                    </h2>
                    {badge && (
                        <Badge variant="secondary" className="text-xs">
                            {badge}
                        </Badge>
                    )}
                </div>
                {description && (
                    <p className="mt-1 text-sm text-muted-foreground">
                        {description}
                    </p>
                )}
            </div>
            <div className="space-y-8">{children}</div>
        </section>
    );
}

function ShowcaseRow({
    title,
    children,
}: {
    title?: string;
    children: React.ReactNode;
}) {
    return (
        <div className="space-y-2">
            {title && (
                <p className="text-xs font-medium tracking-wider text-muted-foreground uppercase">
                    {title}
                </p>
            )}
            <div className="flex flex-wrap items-center gap-3">{children}</div>
        </div>
    );
}

function ShowcaseGrid({
    children,
    cols = 3,
}: {
    children: React.ReactNode;
    cols?: 2 | 3 | 4;
}) {
    return (
        <div
            className={cn('grid gap-4', {
                'grid-cols-1 sm:grid-cols-2': cols === 2,
                'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3': cols === 3,
                'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4': cols === 4,
            })}
        >
            {children}
        </div>
    );
}

// ---------------------------------------------------------------------------
// ComponentPreview — shadcn-style Preview/Code tabs with copy button
// ---------------------------------------------------------------------------

function ComponentPreview({
    title,
    description,
    code,
    children,
    fullWidth = false,
}: {
    title?: string;
    description?: string;
    code: string;
    children: React.ReactNode;
    fullWidth?: boolean;
}) {
    const [copied, setCopied] = React.useState(false);

    function handleCopy() {
        navigator.clipboard.writeText(code);
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
    }

    return (
        <div className="space-y-2">
            {title && (
                <div>
                    <p className="text-sm font-medium">{title}</p>
                    {description && (
                        <p className="text-xs text-muted-foreground">
                            {description}
                        </p>
                    )}
                </div>
            )}
            <div className="overflow-hidden rounded-xl border">
                <Tabs defaultValue="preview">
                    {/* Tab bar */}
                    <div className="flex items-center justify-between border-b bg-muted/40 px-3 py-1.5">
                        <TabsList className="h-7 gap-0.5 bg-transparent p-0">
                            <TabsTrigger
                                value="preview"
                                className="h-6 rounded px-2.5 text-xs data-[state=active]:bg-background data-[state=active]:shadow-sm"
                            >
                                Preview
                            </TabsTrigger>
                            <TabsTrigger
                                value="code"
                                className="h-6 rounded px-2.5 text-xs data-[state=active]:bg-background data-[state=active]:shadow-sm"
                            >
                                Code
                            </TabsTrigger>
                        </TabsList>
                        <button
                            onClick={handleCopy}
                            className="flex items-center gap-1 rounded px-2 py-0.5 text-xs text-muted-foreground transition-colors hover:bg-background hover:text-foreground"
                        >
                            {copied ? (
                                <Check className="size-3" />
                            ) : (
                                <Copy className="size-3" />
                            )}
                            {copied ? 'Copied!' : 'Copy'}
                        </button>
                    </div>
                    {/* Preview tab */}
                    <TabsContent
                        value="preview"
                        className="m-0 flex min-h-[120px] items-center justify-center bg-background p-6"
                    >
                        <div
                            className={
                                fullWidth
                                    ? 'w-full'
                                    : 'flex flex-wrap items-center gap-3'
                            }
                        >
                            {children}
                        </div>
                    </TabsContent>
                    {/* Code tab */}
                    <TabsContent value="code" className="m-0">
                        <pre className="overflow-x-auto bg-zinc-950 p-4 text-xs leading-relaxed text-zinc-100 dark:bg-zinc-900">
                            <code>{code}</code>
                        </pre>
                    </TabsContent>
                </Tabs>
            </div>
        </div>
    );
}

// ---------------------------------------------------------------------------
// Mock data
// ---------------------------------------------------------------------------

const CHART_DATA = [
    { month: 'Jan', revenue: 4200, users: 340 },
    { month: 'Feb', revenue: 5800, users: 420 },
    { month: 'Mar', revenue: 4900, users: 380 },
    { month: 'Apr', revenue: 7200, users: 560 },
    { month: 'May', revenue: 6100, users: 490 },
    { month: 'Jun', revenue: 8400, users: 670 },
];

const SPARKLINE_DATA = [
    { v: 30 },
    { v: 50 },
    { v: 35 },
    { v: 70 },
    { v: 55 },
    { v: 80 },
    { v: 65 },
];

const PIE_DATA = [
    { name: 'Direct', value: 42 },
    { name: 'Social', value: 28 },
    { name: 'Email', value: 18 },
    { name: 'Other', value: 12 },
];

const MOCK_USER = {
    id: '1',
    name: 'Alice Johnson',
    email: 'alice@example.com',
    role: 'Product Designer',
    avatar: undefined,
    initials: 'AJ',
    bio: 'Building delightful experiences, one pixel at a time.',
    status: 'online' as const,
    badges: ['Pro', 'Verified'],
    stats: [
        { label: 'Projects', value: 24 },
        { label: 'Reviews', value: 138 },
        { label: 'Stars', value: '4.9' },
    ],
};

const MOCK_PRICING_FEATURES = [
    { label: 'Up to 10 users', included: true },
    { label: 'API access', included: true },
    { label: 'Custom domain', included: false },
    { label: 'Priority support', included: false },
];

const MOCK_ACTIVITIES = [
    {
        id: '1',
        actor: { name: 'Alice', initials: 'AL' },
        action: 'created a new project',
        target: 'Redesign 2025',
        timestamp: new Date(Date.now() - 60 * 60 * 1000),
    },
    {
        id: '2',
        actor: { name: 'Bob', initials: 'BO' },
        action: 'left a comment on',
        target: 'Homepage spec',
        timestamp: new Date(Date.now() - 3 * 60 * 60 * 1000),
    },
    {
        id: '3',
        actor: { name: 'Carol', initials: 'CA' },
        action: 'closed issue',
        target: '#142 Login bug',
        timestamp: new Date(Date.now() - 8 * 60 * 60 * 1000),
    },
];

const MOCK_METRICS = [
    {
        id: 'm1',
        title: 'Monthly Revenue',
        value: '$84,200',
        description: '+12% from last month',
        trend: { value: 12, direction: 'up' as const },
    },
    {
        id: 'm2',
        title: 'Active Users',
        value: '6,740',
        description: '+8% from last month',
        trend: { value: 8, direction: 'up' as const },
    },
    {
        id: 'm3',
        title: 'Churn Rate',
        value: '2.1%',
        description: '-0.4% improvement',
        trend: { value: 4, direction: 'down' as const },
    },
];

const MOCK_AUDIT_ENTRIES = [
    {
        id: '1',
        actor: {
            id: 'u1',
            name: 'Alice Johnson',
            email: 'alice@example.com',
            initials: 'AJ',
        },
        action: 'user.login',
        target: 'Web session',
        timestamp: new Date(Date.now() - 30 * 60 * 1000).toISOString(),
        variant: 'info' as const,
        metadata: { browser: 'Chrome 122' },
    },
    {
        id: '2',
        actor: {
            id: 'u2',
            name: 'Bob Smith',
            email: 'bob@example.com',
            initials: 'BS',
        },
        action: 'settings.update',
        target: 'Acme Corp',
        timestamp: new Date(Date.now() - 2 * 60 * 60 * 1000).toISOString(),
        variant: 'warning' as const,
        metadata: { field: 'billing_email' },
    },
];

const MOCK_PM_ROLES = [
    { id: 'r1', name: 'Admin' },
    { id: 'r2', name: 'Member' },
];

const MOCK_PM_PERMISSIONS = [
    { id: 'p1', name: 'manage_users', resource: 'Users' },
    { id: 'p2', name: 'view_billing', resource: 'Billing' },
    { id: 'p3', name: 'export_data', resource: 'Data' },
];

const MOCK_PM_GRANTS = {
    r1: { p1: true, p2: true, p3: true },
    r2: { p1: false, p2: false, p3: true },
};

// ---------------------------------------------------------------------------
// Sidebar nav
// ---------------------------------------------------------------------------

function ShowcaseSidebar({ activeId }: { activeId: string }) {
    const [search, setSearch] = React.useState('');
    const filtered = CATEGORIES.filter((c) =>
        c.label.toLowerCase().includes(search.toLowerCase()),
    );

    return (
        <nav className="flex flex-col gap-0.5 p-3">
            <p className="mb-2 px-2 text-xs font-semibold tracking-wider text-muted-foreground uppercase">
                Components
            </p>
            <div className="mb-2 px-2">
                <Input
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                    placeholder="Filter..."
                    className="h-7 text-xs"
                />
            </div>
            {filtered.map((cat) => (
                <a
                    key={cat.id}
                    href={`#${cat.id}`}
                    className={cn(
                        'flex items-center gap-2 rounded-md px-2 py-1.5 text-sm transition-colors',
                        activeId === cat.id
                            ? 'bg-primary/10 font-medium text-primary'
                            : 'text-muted-foreground hover:bg-muted hover:text-foreground',
                    )}
                    onClick={(e) => {
                        e.preventDefault();
                        document.getElementById(cat.id)?.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start',
                        });
                    }}
                >
                    {cat.icon}
                    {cat.label}
                </a>
            ))}
            {filtered.length === 0 && (
                <p className="px-2 py-4 text-center text-xs text-muted-foreground">
                    No results
                </p>
            )}
        </nav>
    );
}

// ---------------------------------------------------------------------------
// Sticky top bar
// ---------------------------------------------------------------------------

function ThemeBar({
    theme,
}: {
    theme: ActiveTheme;
    onOpenCustomizer?: () => void;
}) {
    return (
        <div className="flex h-full items-center justify-between gap-4 px-4 text-xs">
            <div className="flex items-center gap-4">
                <span className="font-semibold text-muted-foreground">
                    Live theme:
                </span>
                <ThemePill label="Dark" value={theme.dark} />
                <ThemePill label="Primary" value={theme.primary} />
                <ThemePill label="Radius" value={theme.radius} />
                <ThemePill label="Skin" value={theme.skin} />
            </div>
            <div className="hidden items-center gap-1 text-muted-foreground sm:flex">
                <span>Switch themes with the</span>
                <span className="font-medium text-primary">
                    Theme Customizer
                </span>
                <span>on the right edge →</span>
            </div>
        </div>
    );
}

function ThemePill({ label, value }: { label: string; value: string }) {
    return (
        <span className="flex items-center gap-1">
            <span className="text-muted-foreground">{label}:</span>
            <Badge variant="secondary" className="h-4 px-1.5 text-[10px]">
                {value}
            </Badge>
        </span>
    );
}

// ---------------------------------------------------------------------------
// Section: Foundation
// ---------------------------------------------------------------------------

const DARK_PALETTE_COLORS: Record<string, string> = {
    navy: '#1e2d4a',
    mirage: '#1a1d2e',
    mint: '#1a2e2a',
    black: '#0a0a0a',
    cinder: '#1c1a1a',
};

const PRIMARY_PALETTE_COLORS: Record<string, string> = {
    indigo: '#6366f1',
    blue: '#3b82f6',
    green: '#22c55e',
    amber: '#f59e0b',
    purple: '#a855f7',
    rose: '#f43f5e',
};

function FoundationSection() {
    return (
        <ShowcaseSection
            id="foundation"
            title="Foundation"
            description="Design tokens — colors, typography, shadows, and spacing that power the entire system."
        >
            <ShowcaseRow title="Dark Themes">
                {DARK_THEMES.map((name) => (
                    <div key={name} className="flex items-center gap-2">
                        <span
                            className="size-6 rounded-full border"
                            style={{ background: DARK_PALETTE_COLORS[name] }}
                        />
                        <span className="text-xs text-muted-foreground capitalize">
                            {name}
                        </span>
                    </div>
                ))}
            </ShowcaseRow>

            <ShowcaseRow title="Primary Colors">
                {PRIMARY_COLORS.map((name) => (
                    <div key={name} className="flex items-center gap-2">
                        <span
                            className="size-6 rotate-45 rounded-sm border"
                            style={{ background: PRIMARY_PALETTE_COLORS[name] }}
                        />
                        <span className="text-xs text-muted-foreground capitalize">
                            {name}
                        </span>
                    </div>
                ))}
            </ShowcaseRow>

            <ShowcaseRow title="Semantic Colors">
                {[
                    { label: 'Primary', cls: 'bg-primary' },
                    { label: 'Secondary', cls: 'bg-secondary' },
                    { label: 'Info', cls: 'bg-[var(--color-info)]' },
                    { label: 'Success', cls: 'bg-[var(--color-success)]' },
                    { label: 'Warning', cls: 'bg-[var(--color-warning)]' },
                    { label: 'Error', cls: 'bg-[var(--color-error)]' },
                    { label: 'Surface 1', cls: 'bg-[var(--color-surface-1)]' },
                    { label: 'Surface 2', cls: 'bg-[var(--color-surface-2)]' },
                    { label: 'Surface 3', cls: 'bg-[var(--color-surface-3)]' },
                ].map(({ label, cls }) => (
                    <div
                        key={label}
                        className="flex flex-col items-center gap-1"
                    >
                        <span className={cn('size-8 rounded border', cls)} />
                        <span className="text-[10px] text-muted-foreground">
                            {label}
                        </span>
                    </div>
                ))}
            </ShowcaseRow>

            <ShowcaseRow title="Typography Scale">
                <div className="w-full space-y-1">
                    {[
                        { cls: 'text-[var(--text-tiny)]', label: 'text-tiny' },
                        { cls: 'text-xs', label: 'text-xs' },
                        { cls: 'text-sm', label: 'text-sm' },
                        { cls: 'text-base', label: 'text-base' },
                        { cls: 'text-lg', label: 'text-lg' },
                        { cls: 'text-xl', label: 'text-xl' },
                        { cls: 'text-2xl', label: 'text-2xl' },
                    ].map(({ cls, label }) => (
                        <div key={label} className="flex items-baseline gap-3">
                            <span className="w-24 shrink-0 font-mono text-[10px] text-muted-foreground">
                                {label}
                            </span>
                            <span className={cn('text-foreground', cls)}>
                                The quick brown fox
                            </span>
                        </div>
                    ))}
                </div>
            </ShowcaseRow>

            <ShowcaseRow title="Shadows">
                {[
                    { label: 'soft', cls: 'shadow-[var(--shadow-soft)]' },
                    { label: 'sm', cls: 'shadow-sm' },
                    { label: 'md', cls: 'shadow-md' },
                    { label: 'lg', cls: 'shadow-lg' },
                    { label: 'xl', cls: 'shadow-xl' },
                ].map(({ label, cls }) => (
                    <div
                        key={label}
                        className={cn(
                            'flex h-12 w-20 items-center justify-center rounded-md bg-card text-xs text-muted-foreground',
                            cls,
                        )}
                    >
                        {label}
                    </div>
                ))}
            </ShowcaseRow>
        </ShowcaseSection>
    );
}

// ---------------------------------------------------------------------------
// Section: Layout
// ---------------------------------------------------------------------------

function LayoutSection() {
    return (
        <ShowcaseSection
            id="layout"
            title="Layout"
            description="Container widths, stacks, grids, and dividers."
        >
            <ShowcaseRow title="Container (max-widths)">
                {(['sm', 'md', 'lg', 'xl', '2xl'] as const).map((size) => (
                    <div
                        key={size}
                        className="flex h-8 items-center justify-center rounded border bg-muted/50 px-4 text-xs text-muted-foreground"
                    >
                        container-{size}
                    </div>
                ))}
            </ShowcaseRow>

            <ShowcaseRow title="Stack / HStack / VStack">
                <div className="flex items-start gap-6">
                    <div className="flex flex-col gap-2">
                        <p className="text-[10px] text-muted-foreground">
                            VStack gap-2
                        </p>
                        <div className="flex flex-col gap-2">
                            {['A', 'B', 'C'].map((l) => (
                                <span
                                    key={l}
                                    className="flex h-6 w-10 items-center justify-center rounded bg-primary/20 text-xs"
                                >
                                    {l}
                                </span>
                            ))}
                        </div>
                    </div>
                    <div className="flex flex-col gap-2">
                        <p className="text-[10px] text-muted-foreground">
                            HStack gap-2
                        </p>
                        <div className="flex items-center gap-2">
                            {['X', 'Y', 'Z'].map((l) => (
                                <span
                                    key={l}
                                    className="flex h-6 w-10 items-center justify-center rounded bg-secondary/40 text-xs"
                                >
                                    {l}
                                </span>
                            ))}
                        </div>
                    </div>
                </div>
            </ShowcaseRow>

            <ShowcaseRow title="Grid">
                <div className="grid w-full grid-cols-4 gap-2">
                    {Array.from({ length: 8 }, (_, i) => (
                        <div
                            key={i}
                            className="flex h-8 items-center justify-center rounded bg-muted text-xs text-muted-foreground"
                        >
                            col {i + 1}
                        </div>
                    ))}
                </div>
            </ShowcaseRow>

            <ShowcaseRow title="Dividers">
                <div className="w-full space-y-4">
                    <Separator />
                    <Separator className="border-dashed" />
                </div>
            </ShowcaseRow>
        </ShowcaseSection>
    );
}

// ---------------------------------------------------------------------------
// Section: Shells
// ---------------------------------------------------------------------------

function ShellsSection() {
    return (
        <ShowcaseSection
            id="shells"
            title="Shells"
            description="Full-page layout wrappers for common application patterns."
        >
            <ShowcaseRow>
                <div className="w-full space-y-2">
                    <p className="text-xs text-muted-foreground">
                        Shells are full-page layout wrappers. This page itself
                        uses{' '}
                        <code className="rounded bg-muted px-1 font-mono text-[11px]">
                            AppShell
                        </code>{' '}
                        with a sticky sidebar and header.
                    </p>
                    <ShowcaseGrid cols={3}>
                        {[
                            {
                                name: 'AppShell',
                                desc: 'Sidebar + header + main + optional right panel',
                            },
                            {
                                name: 'DashboardLayout',
                                desc: 'Two-column responsive dashboard',
                            },
                            {
                                name: 'MarketingLayout',
                                desc: 'Marketing site layout with hero slots',
                            },
                            {
                                name: 'MasterDetail',
                                desc: 'List + detail split panel',
                            },
                            {
                                name: 'SplitView',
                                desc: 'Resizable horizontal split',
                            },
                        ].map(({ name, desc }) => (
                            <Card key={name}>
                                <CardHeader className="pb-2">
                                    <CardTitle className="text-sm">
                                        {name}
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <p className="text-xs text-muted-foreground">
                                        {desc}
                                    </p>
                                </CardContent>
                            </Card>
                        ))}
                    </ShowcaseGrid>
                </div>
            </ShowcaseRow>
        </ShowcaseSection>
    );
}

// ---------------------------------------------------------------------------
// Section: Navigation
// ---------------------------------------------------------------------------

function NavigationSection() {
    return (
        <ShowcaseSection
            id="navigation"
            title="Navigation"
            description="Tabs, breadcrumbs, and pagination for moving between views."
            badge="3 components"
        >
            <ComponentPreview
                title="Tabs"
                description="Switch between related content panels."
                code={`<Tabs defaultValue="tab1">
  <TabsList>
    <TabsTrigger value="tab1">Overview</TabsTrigger>
    <TabsTrigger value="tab2">Analytics</TabsTrigger>
    <TabsTrigger value="tab3">Settings</TabsTrigger>
  </TabsList>
  <TabsContent value="tab1">Overview content.</TabsContent>
  <TabsContent value="tab2">Analytics content.</TabsContent>
  <TabsContent value="tab3">Settings content.</TabsContent>
</Tabs>`}
                fullWidth
            >
                <Tabs defaultValue="tab1" className="w-full">
                    <TabsList>
                        <TabsTrigger value="tab1">Overview</TabsTrigger>
                        <TabsTrigger value="tab2">Analytics</TabsTrigger>
                        <TabsTrigger value="tab3">Settings</TabsTrigger>
                    </TabsList>
                    <TabsContent
                        value="tab1"
                        className="mt-3 text-sm text-muted-foreground"
                    >
                        Overview tab content.
                    </TabsContent>
                    <TabsContent
                        value="tab2"
                        className="mt-3 text-sm text-muted-foreground"
                    >
                        Analytics tab content.
                    </TabsContent>
                    <TabsContent
                        value="tab3"
                        className="mt-3 text-sm text-muted-foreground"
                    >
                        Settings tab content.
                    </TabsContent>
                </Tabs>
            </ComponentPreview>

            <ComponentPreview
                title="Breadcrumbs"
                description="Show the current location within the app hierarchy."
                code={`<nav className="flex items-center gap-1 text-sm">
  <span className="text-muted-foreground hover:text-foreground cursor-pointer">Home</span>
  <span className="text-muted-foreground">/</span>
  <span className="text-muted-foreground hover:text-foreground cursor-pointer">Settings</span>
  <span className="text-muted-foreground">/</span>
  <span className="font-medium">Profile</span>
</nav>`}
            >
                <nav className="flex items-center gap-1 text-sm">
                    {['Home', 'Settings', 'Profile'].map((crumb, i, arr) => (
                        <React.Fragment key={crumb}>
                            <span
                                className={
                                    i === arr.length - 1
                                        ? 'font-medium'
                                        : 'cursor-pointer text-muted-foreground hover:text-foreground'
                                }
                            >
                                {crumb}
                            </span>
                            {i < arr.length - 1 && (
                                <span className="text-muted-foreground">/</span>
                            )}
                        </React.Fragment>
                    ))}
                </nav>
            </ComponentPreview>

            <ComponentPreview
                title="Pagination"
                description="Navigate between pages of content."
                code={`<div className="flex items-center gap-1">
  {['«', '1', '2', '3', '…', '10', '»'].map((p) => (
    <button
      key={p}
      className={cn(
        'flex h-8 min-w-[2rem] items-center justify-center rounded-md border px-2 text-sm',
        p === '2' ? 'bg-primary text-primary-foreground' : 'hover:bg-muted',
      )}
    >
      {p}
    </button>
  ))}
</div>`}
            >
                <div className="flex items-center gap-1">
                    {['«', '1', '2', '3', '…', '10', '»'].map((p) => (
                        <button
                            key={p}
                            className={cn(
                                'flex h-8 min-w-[2rem] items-center justify-center rounded-md border px-2 text-sm',
                                p === '2'
                                    ? 'bg-primary text-primary-foreground'
                                    : 'hover:bg-muted',
                            )}
                        >
                            {p}
                        </button>
                    ))}
                </div>
            </ComponentPreview>
        </ShowcaseSection>
    );
}

// ---------------------------------------------------------------------------
// Section: Buttons & Actions
// ---------------------------------------------------------------------------

function ButtonsSection() {
    return (
        <ShowcaseSection
            id="buttons"
            title="Buttons & Actions"
            description="Interactive controls for triggering actions and navigating."
            badge="8 variants"
        >
            <ComponentPreview
                title="Button Variants"
                description="All available visual styles for the Button component."
                code={`<Button variant="default">Default</Button>
<Button variant="secondary">Secondary</Button>
<Button variant="outline">Outline</Button>
<Button variant="ghost">Ghost</Button>
<Button variant="link">Link</Button>
<Button variant="destructive">Destructive</Button>
<Button variant="soft">Soft</Button>
<Button variant="flat">Flat</Button>`}
            >
                {(
                    [
                        'default',
                        'secondary',
                        'outline',
                        'ghost',
                        'link',
                        'destructive',
                        'soft',
                        'flat',
                    ] as const
                ).map((v) => (
                    <Button key={v} variant={v} size="sm">
                        {v}
                    </Button>
                ))}
            </ComponentPreview>

            <ComponentPreview
                title="Button Sizes"
                description="Three size options to fit different contexts."
                code={`<Button size="sm">Small</Button>
<Button size="default">Default</Button>
<Button size="lg">Large</Button>`}
            >
                {(['sm', 'default', 'lg'] as const).map((s) => (
                    <Button key={s} size={s}>
                        Size {s}
                    </Button>
                ))}
            </ComponentPreview>

            <ComponentPreview
                title="Button States"
                description="Disabled and loading states for async operations."
                code={`<Button disabled>Disabled</Button>
<Button isLoading>Loading</Button>
<Button variant="outline">
  <Check className="size-4" /> With icon
</Button>`}
            >
                <Button disabled>Disabled</Button>
                <Button isLoading>Loading</Button>
                <Button variant="outline">
                    <Check className="size-4" /> With icon
                </Button>
            </ComponentPreview>

            <ComponentPreview
                title="Tooltips on Buttons"
                description="Attach tooltips to any element with TooltipProvider."
                code={`<TooltipProvider>
  <Tooltip>
    <TooltipTrigger asChild>
      <Button variant="outline" size="sm">Hover (top)</Button>
    </TooltipTrigger>
    <TooltipContent side="top">Tooltip on top</TooltipContent>
  </Tooltip>
</TooltipProvider>`}
            >
                <TooltipProvider>
                    {(['top', 'right', 'bottom', 'left'] as const).map(
                        (side) => (
                            <Tooltip key={side}>
                                <TooltipTrigger asChild>
                                    <Button variant="outline" size="sm">
                                        Hover ({side})
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent side={side}>
                                    Tooltip on {side}
                                </TooltipContent>
                            </Tooltip>
                        ),
                    )}
                </TooltipProvider>
            </ComponentPreview>
        </ShowcaseSection>
    );
}

// ---------------------------------------------------------------------------
// Section: Forms
// ---------------------------------------------------------------------------

function FormsSection() {
    return (
        <ShowcaseSection
            id="forms"
            title="Forms"
            description="Input controls for collecting user data."
            badge="3 components"
        >
            <ComponentPreview
                title="Text Inputs"
                description="Standard, password, and disabled input states."
                code={`<Input placeholder="Enter text…" />
<Input type="password" placeholder="Password" />
<Input placeholder="Disabled" disabled />`}
                fullWidth
            >
                <div className="grid w-full grid-cols-1 gap-4 sm:grid-cols-3">
                    <div className="space-y-2">
                        <label className="text-sm font-medium">
                            Text Input
                        </label>
                        <Input placeholder="Enter text…" />
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium">
                            Password Input
                        </label>
                        <Input type="password" placeholder="Password" />
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium">Disabled</label>
                        <Input placeholder="Disabled" disabled />
                    </div>
                </div>
            </ComponentPreview>

            <ComponentPreview
                title="Switches"
                description="Toggle between two states."
                code={`<div className="flex items-center gap-2">
  <Switch id="sw1" defaultChecked />
  <label htmlFor="sw1">Enabled</label>
</div>
<div className="flex items-center gap-2">
  <Switch id="sw2" />
  <label htmlFor="sw2">Disabled (off)</label>
</div>`}
            >
                <div className="flex items-center gap-6">
                    <div className="flex items-center gap-2">
                        <Switch id="sw1" defaultChecked />
                        <label htmlFor="sw1" className="text-sm">
                            Enabled
                        </label>
                    </div>
                    <div className="flex items-center gap-2">
                        <Switch id="sw2" />
                        <label htmlFor="sw2" className="text-sm">
                            Disabled (off)
                        </label>
                    </div>
                </div>
            </ComponentPreview>

            <ComponentPreview
                title="Progress"
                description="Indicate completion of a task or process."
                code={`<Progress value={33} />
<Progress value={66} />
<Progress value={100} />`}
                fullWidth
            >
                <div className="w-full max-w-sm space-y-2">
                    <Progress value={33} />
                    <Progress value={66} />
                    <Progress value={100} />
                </div>
            </ComponentPreview>
        </ShowcaseSection>
    );
}

// ---------------------------------------------------------------------------
// Section: Data Display
// ---------------------------------------------------------------------------

function DataDisplaySection() {
    return (
        <ShowcaseSection
            id="data-display"
            title="Data Display"
            description="Components for presenting information clearly."
            badge="3 components"
        >
            <ComponentPreview
                title="Badges"
                description="Small status indicators and labels."
                code={`<Badge variant="default">default</Badge>
<Badge variant="secondary">secondary</Badge>
<Badge variant="outline">outline</Badge>
<Badge variant="destructive">destructive</Badge>`}
            >
                {(
                    ['default', 'secondary', 'outline', 'destructive'] as const
                ).map((v) => (
                    <Badge key={v} variant={v}>
                        {v}
                    </Badge>
                ))}
            </ComponentPreview>

            <ComponentPreview
                title="Avatars"
                description="User profile pictures with image and fallback support."
                code={`<Avatar>
  <AvatarImage src="https://github.com/shadcn.png" alt="shadcn" />
  <AvatarFallback>SC</AvatarFallback>
</Avatar>
<Avatar>
  <AvatarFallback>AJ</AvatarFallback>
</Avatar>`}
            >
                <Avatar>
                    <AvatarImage
                        src="https://github.com/shadcn.png"
                        alt="shadcn"
                    />
                    <AvatarFallback>SC</AvatarFallback>
                </Avatar>
                <Avatar>
                    <AvatarFallback>AJ</AvatarFallback>
                </Avatar>
                <Avatar>
                    <AvatarFallback>BK</AvatarFallback>
                </Avatar>
            </ComponentPreview>

            <ComponentPreview
                title="Cards"
                description="Surface variants: default (shadow), bordered, and flat."
                code={`<Card>
  <CardHeader>
    <CardTitle>Default Card</CardTitle>
    <CardDescription>Shadow skin (default)</CardDescription>
  </CardHeader>
  <CardContent>Card body content.</CardContent>
</Card>

<Card skin="bordered">
  <CardHeader>
    <CardTitle>Bordered Card</CardTitle>
  </CardHeader>
</Card>

<Card skin="flat">
  <CardHeader>
    <CardTitle>Flat Card</CardTitle>
  </CardHeader>
</Card>`}
                fullWidth
            >
                <ShowcaseGrid cols={3}>
                    <Card>
                        <CardHeader>
                            <CardTitle>Default Card</CardTitle>
                            <CardDescription>
                                A standard card component
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <p className="text-sm text-muted-foreground">
                                Card body content.
                            </p>
                        </CardContent>
                    </Card>
                    <Card skin="bordered">
                        <CardHeader>
                            <CardTitle>Bordered Card</CardTitle>
                            <CardDescription>
                                Bordered skin variant
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <p className="text-sm text-muted-foreground">
                                Card body content.
                            </p>
                        </CardContent>
                    </Card>
                    <Card skin="flat">
                        <CardHeader>
                            <CardTitle>Flat Card</CardTitle>
                            <CardDescription>Flat skin variant</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <p className="text-sm text-muted-foreground">
                                Card body content.
                            </p>
                        </CardContent>
                    </Card>
                </ShowcaseGrid>
            </ComponentPreview>
        </ShowcaseSection>
    );
}

// ---------------------------------------------------------------------------
// Section: Feedback
// ---------------------------------------------------------------------------

function FeedbackSection() {
    return (
        <ShowcaseSection
            id="feedback"
            title="Feedback"
            description="Alerts, skeletons, and spinners to communicate state to users."
            badge="3 components"
        >
            <ComponentPreview
                title="Alerts"
                description="Contextual messages for default and destructive states."
                code={`<Alert>
  <AlertTriangle className="size-4" />
  <AlertTitle>Heads up!</AlertTitle>
  <AlertDescription>Default alert — informational message.</AlertDescription>
</Alert>

<Alert variant="destructive">
  <AlertTriangle className="size-4" />
  <AlertTitle>Error</AlertTitle>
  <AlertDescription>Something went wrong. Please try again.</AlertDescription>
</Alert>`}
                fullWidth
            >
                <div className="w-full space-y-3">
                    <Alert>
                        <AlertTriangle className="size-4" />
                        <AlertTitle>Heads up!</AlertTitle>
                        <AlertDescription>
                            Default alert — informational message.
                        </AlertDescription>
                    </Alert>
                    <Alert variant="destructive">
                        <AlertTriangle className="size-4" />
                        <AlertTitle>Error</AlertTitle>
                        <AlertDescription>
                            Something went wrong. Please try again.
                        </AlertDescription>
                    </Alert>
                </div>
            </ComponentPreview>

            <ComponentPreview
                title="Skeletons"
                description="Animated placeholders while content is loading."
                code={`<Skeleton className="h-4 w-3/4" />
<Skeleton className="h-4 w-full" />
<Skeleton className="h-4 w-1/2" />`}
                fullWidth
            >
                <div className="w-full max-w-sm space-y-2">
                    <Skeleton className="h-4 w-3/4" />
                    <Skeleton className="h-4 w-full" />
                    <Skeleton className="h-4 w-1/2" />
                </div>
            </ComponentPreview>

            <ComponentPreview
                title="Spinners"
                description="Indicate an ongoing operation with three sizes."
                code={`<Spinner size="sm" />
<Spinner size="md" />
<Spinner size="lg" />`}
            >
                {(['sm', 'md', 'lg'] as const).map((size) => (
                    <Spinner key={size} size={size} />
                ))}
            </ComponentPreview>
        </ShowcaseSection>
    );
}

// ---------------------------------------------------------------------------
// Section: Overlay
// ---------------------------------------------------------------------------

function OverlaySection() {
    const [confirmOpen, setConfirmOpen] = React.useState(false);

    return (
        <ShowcaseSection
            id="overlay"
            title="Overlay"
            description="Modals, panels, popovers, and menus that appear on top of content."
            badge="5 components"
        >
            <ComponentPreview
                title="Dialog"
                description="A modal dialog that interrupts the user with important content."
                code={`<Dialog>
  <DialogTrigger asChild>
    <Button variant="outline">Open Dialog</Button>
  </DialogTrigger>
  <DialogContent>
    <DialogHeader>
      <DialogTitle>Are you sure?</DialogTitle>
      <DialogDescription>
        This action cannot be undone.
      </DialogDescription>
    </DialogHeader>
  </DialogContent>
</Dialog>`}
            >
                <Dialog>
                    <DialogTrigger asChild>
                        <Button variant="outline">Open Dialog</Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Dialog Title</DialogTitle>
                            <DialogDescription>
                                This is a standard dialog. It focuses the user's
                                attention on a single task or message.
                            </DialogDescription>
                        </DialogHeader>
                        <p className="text-sm text-muted-foreground">
                            Dialog body content goes here. You can put forms,
                            text, or any other content.
                        </p>
                    </DialogContent>
                </Dialog>
            </ComponentPreview>

            <ComponentPreview
                title="Sheet"
                description="A slide-over panel anchored to the edge of the screen."
                code={`<Sheet>
  <SheetTrigger asChild>
    <Button variant="outline">Open Sheet</Button>
  </SheetTrigger>
  <SheetContent>
    <SheetHeader>
      <SheetTitle>Sheet Title</SheetTitle>
      <SheetDescription>Sheet description.</SheetDescription>
    </SheetHeader>
  </SheetContent>
</Sheet>`}
            >
                <Sheet>
                    <SheetTrigger asChild>
                        <Button variant="outline">Open Sheet</Button>
                    </SheetTrigger>
                    <SheetContent>
                        <SheetHeader>
                            <SheetTitle>Sheet Title</SheetTitle>
                            <SheetDescription>
                                Sheets slide in from the side. Useful for forms,
                                filters, or detail views.
                            </SheetDescription>
                        </SheetHeader>
                        <p className="mt-4 text-sm text-muted-foreground">
                            Sheet body content goes here.
                        </p>
                    </SheetContent>
                </Sheet>
            </ComponentPreview>

            <ComponentPreview
                title="Popover"
                description="A floating panel anchored to a trigger element."
                code={`<Popover>
  <PopoverTrigger asChild>
    <Button variant="outline">Open Popover</Button>
  </PopoverTrigger>
  <PopoverContent>
    <p className="text-sm">Popover content here.</p>
  </PopoverContent>
</Popover>`}
            >
                <Popover>
                    <PopoverTrigger asChild>
                        <Button variant="outline">Open Popover</Button>
                    </PopoverTrigger>
                    <PopoverContent>
                        <p className="text-sm font-medium">Popover title</p>
                        <p className="mt-1 text-xs text-muted-foreground">
                            Use popovers for contextual information or quick
                            actions.
                        </p>
                    </PopoverContent>
                </Popover>
            </ComponentPreview>

            <ComponentPreview
                title="Dropdown Menu"
                description="A contextual menu of actions triggered by a button."
                code={`<DropdownMenu>
  <DropdownMenuTrigger asChild>
    <Button variant="outline">Open Menu</Button>
  </DropdownMenuTrigger>
  <DropdownMenuContent>
    <DropdownMenuItem>Profile</DropdownMenuItem>
    <DropdownMenuItem>Settings</DropdownMenuItem>
    <DropdownMenuSeparator />
    <DropdownMenuItem>Log out</DropdownMenuItem>
  </DropdownMenuContent>
</DropdownMenu>`}
            >
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <Button variant="outline">Open Menu</Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent>
                        <DropdownMenuItem>Profile</DropdownMenuItem>
                        <DropdownMenuItem>Settings</DropdownMenuItem>
                        <DropdownMenuItem>Billing</DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem className="text-destructive">
                            Log out
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </ComponentPreview>

            <ComponentPreview
                title="Confirm Dialog"
                description="A pre-built confirmation dialog for destructive actions."
                code={`const [open, setOpen] = React.useState(false);

<Button variant="destructive" onClick={() => setOpen(true)}>
  Delete Item
</Button>

<ConfirmDialog
  open={open}
  onOpenChange={setOpen}
  title="Delete this item?"
  description="This action cannot be undone."
  confirmLabel="Delete"
  variant="destructive"
  onConfirm={() => setOpen(false)}
/>`}
            >
                <Button
                    variant="destructive"
                    onClick={() => setConfirmOpen(true)}
                >
                    Delete Item
                </Button>
                <ConfirmDialog
                    open={confirmOpen}
                    onOpenChange={setConfirmOpen}
                    title="Delete this item?"
                    description="This action cannot be undone. All associated data will be permanently removed."
                    confirmLabel="Delete"
                    variant="destructive"
                    onConfirm={() => setConfirmOpen(false)}
                />
            </ComponentPreview>
        </ShowcaseSection>
    );
}

// ---------------------------------------------------------------------------
// Section: Charts
// ---------------------------------------------------------------------------

function ChartsSection() {
    return (
        <ShowcaseSection
            id="charts"
            title="Charts"
            description="Data visualisation components built on Recharts."
        >
            <ShowcaseGrid cols={2}>
                <Card>
                    <CardHeader className="pb-2">
                        <CardTitle className="text-sm">Area Chart</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <AreaChart
                            data={CHART_DATA}
                            dataKeys={['revenue']}
                            xKey="month"
                            height={180}
                            showGrid
                            showTooltip
                        />
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="pb-2">
                        <CardTitle className="text-sm">Bar Chart</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <BarChart
                            data={CHART_DATA}
                            dataKeys={['users']}
                            xKey="month"
                            height={180}
                            showGrid
                            showTooltip
                        />
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="pb-2">
                        <CardTitle className="text-sm">
                            Pie / Donut Chart
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <PieChart
                            data={PIE_DATA}
                            donut
                            height={180}
                            showLegend
                            showTooltip
                        />
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="pb-2">
                        <CardTitle className="text-sm">
                            Gauge + Sparkline + Progress Ring
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="flex items-center gap-6">
                        <GaugeChart
                            value={72}
                            max={100}
                            label="Score"
                            size={100}
                        />
                        <div className="flex flex-col gap-3">
                            <Sparkline
                                data={SPARKLINE_DATA}
                                dataKey="v"
                                height={40}
                                variant="area"
                            />
                            <ProgressRing
                                value={68}
                                size={64}
                                strokeWidth={8}
                                label="68%"
                            />
                        </div>
                    </CardContent>
                </Card>
            </ShowcaseGrid>
        </ShowcaseSection>
    );
}

// ---------------------------------------------------------------------------
// Section: Maps
// ---------------------------------------------------------------------------

function MapsSection() {
    return (
        <ShowcaseSection
            id="maps"
            title="Maps"
            description="Map components using OpenFreeMap tiles — no API key required."
        >
            <ShowcaseRow>
                <div className="w-full space-y-2">
                    <p className="text-xs text-muted-foreground">
                        Available: BaseMap, MarkersMap, ClustersMap, RoutesMap,
                        AnalyticsMap, TrackingMap, LocationPicker.
                    </p>
                    <ShowcaseGrid cols={3}>
                        {[
                            { name: 'BaseMap', desc: 'Base map with controls' },
                            {
                                name: 'MarkersMap',
                                desc: 'Clickable markers with popups',
                            },
                            {
                                name: 'ClustersMap',
                                desc: 'Point clustering layer',
                            },
                            { name: 'RoutesMap', desc: 'Route polylines' },
                            {
                                name: 'AnalyticsMap',
                                desc: 'Bubble / heatmap data',
                            },
                            {
                                name: 'TrackingMap',
                                desc: 'Real-time asset tracking',
                            },
                        ].map(({ name, desc }) => (
                            <Card key={name}>
                                <CardHeader className="pb-2">
                                    <CardTitle className="flex items-center gap-2 text-sm">
                                        <Map className="size-3.5" />
                                        {name}
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <p className="text-xs text-muted-foreground">
                                        {desc}
                                    </p>
                                </CardContent>
                            </Card>
                        ))}
                    </ShowcaseGrid>
                </div>
            </ShowcaseRow>
        </ShowcaseSection>
    );
}

// ---------------------------------------------------------------------------
// Section: AI
// ---------------------------------------------------------------------------

function AiSection() {
    return (
        <ShowcaseSection
            id="ai"
            title="AI"
            description="Components for building AI-powered interfaces and chat UIs."
        >
            <ShowcaseGrid cols={2}>
                <Card>
                    <CardHeader className="pb-2">
                        <CardTitle className="text-sm">StreamingText</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <StreamingText
                            text="Building the future with AI, one token at a time."
                            isStreaming={false}
                            speed={30}
                        />
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="pb-2">
                        <CardTitle className="text-sm">
                            ThinkingIndicator (3 variants)
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="flex items-center gap-6">
                        <div className="flex flex-col items-center gap-1">
                            <ThinkingIndicator variant="dots" />
                            <span className="text-[10px] text-muted-foreground">
                                dots
                            </span>
                        </div>
                        <div className="flex flex-col items-center gap-1">
                            <ThinkingIndicator variant="pulse" />
                            <span className="text-[10px] text-muted-foreground">
                                pulse
                            </span>
                        </div>
                        <div className="flex flex-col items-center gap-1">
                            <ThinkingIndicator variant="bars" />
                            <span className="text-[10px] text-muted-foreground">
                                bars
                            </span>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="pb-2">
                        <CardTitle className="text-sm">
                            ConfidenceScore
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-3">
                        <ConfidenceScore score={0.92} showLabel size="md" />
                        <ConfidenceScore score={0.61} showLabel size="md" />
                        <ConfidenceScore score={0.28} showLabel size="md" />
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="pb-2">
                        <CardTitle className="text-sm">TokenUsage</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <TokenUsageDisplay
                            usage={{
                                prompt: 1240,
                                completion: 380,
                                total: 1620,
                            }}
                            maxTokens={4096}
                        />
                    </CardContent>
                </Card>
            </ShowcaseGrid>

            <ShowcaseRow title="Other AI Components">
                <div className="w-full">
                    <p className="text-xs text-muted-foreground">
                        Also available: AssistantRuntimeProvider,
                        AssistantThread, AssistantModal, AssistantSidebar,
                        CodeBlock, MarkdownResponse, ToolCallCard,
                        AIResponseCard, AIInsightCard, EntityHighlight,
                        AISummaryCard, PredictionWidget, AnomalyAlert,
                        AgentStatus, ModelSelector, PromptInput, VoiceInput,
                        ContextDrawer.
                    </p>
                </div>
            </ShowcaseRow>
        </ShowcaseSection>
    );
}

// ---------------------------------------------------------------------------
// Section: SaaS
// ---------------------------------------------------------------------------

function SaasSection() {
    return (
        <ShowcaseSection
            id="saas"
            title="SaaS"
            description="Subscription, feature gating, and usage metering components."
        >
            <div className="space-y-4">
                <TrialBanner
                    daysRemaining={5}
                    storageKey="showcase-trial-banner"
                />

                <ShowcaseGrid cols={2}>
                    <div className="space-y-2">
                        <p className="text-xs font-medium tracking-wider text-muted-foreground uppercase">
                            FeatureGate (locked)
                        </p>
                        <FeatureGate
                            hasAccess={false}
                            feature="Analytics Pro"
                            onUpgrade={() => {}}
                        >
                            <p>This would be visible content.</p>
                        </FeatureGate>
                    </div>

                    <div className="space-y-2">
                        <p className="text-xs font-medium tracking-wider text-muted-foreground uppercase">
                            FeatureGate (unlocked)
                        </p>
                        <FeatureGate hasAccess={true} feature="Analytics Pro">
                            <Card>
                                <CardContent className="pt-4">
                                    <p className="text-sm text-muted-foreground">
                                        Content visible when access granted.
                                    </p>
                                </CardContent>
                            </Card>
                        </FeatureGate>
                    </div>
                </ShowcaseGrid>

                <div className="space-y-2">
                    <p className="text-xs font-medium tracking-wider text-muted-foreground uppercase">
                        UsageMeter
                    </p>
                    <UsageMeter
                        label="API Calls"
                        used={7200}
                        limit={10000}
                        unit="calls"
                    />
                </div>
            </div>
        </ShowcaseSection>
    );
}

// ---------------------------------------------------------------------------
// Section: Admin
// ---------------------------------------------------------------------------

function AdminSection() {
    return (
        <ShowcaseSection
            id="admin"
            title="Admin"
            description="Audit logs and permission management for back-office interfaces."
        >
            <div className="space-y-6">
                <div className="space-y-2">
                    <p className="text-xs font-medium tracking-wider text-muted-foreground uppercase">
                        Audit Log Viewer
                    </p>
                    <AuditLogViewer entries={MOCK_AUDIT_ENTRIES} />
                </div>

                <div className="space-y-2">
                    <p className="text-xs font-medium tracking-wider text-muted-foreground uppercase">
                        Permission Matrix
                    </p>
                    <PermissionMatrix
                        roles={MOCK_PM_ROLES}
                        permissions={MOCK_PM_PERMISSIONS}
                        grants={MOCK_PM_GRANTS}
                    />
                </div>
            </div>
        </ShowcaseSection>
    );
}

// ---------------------------------------------------------------------------
// Section: Composed
// ---------------------------------------------------------------------------

function ComposedSection() {
    return (
        <ShowcaseSection
            id="composed"
            title="Composed"
            description="Higher-level components assembled from primitives."
        >
            <div className="space-y-6">
                <div className="space-y-2">
                    <p className="text-xs font-medium tracking-wider text-muted-foreground uppercase">
                        MetricDashboard
                    </p>
                    <MetricDashboard
                        metrics={MOCK_METRICS}
                        chartData={CHART_DATA}
                        chartXKey="month"
                        chartDataKeys={['revenue']}
                        chartTitle="Revenue"
                        columns={3}
                    />
                </div>

                <ShowcaseGrid cols={2}>
                    <div className="space-y-2">
                        <p className="text-xs font-medium tracking-wider text-muted-foreground uppercase">
                            UserCard variants
                        </p>
                        <div className="space-y-3">
                            <UserCard user={MOCK_USER} variant="compact" />
                            <UserCard user={MOCK_USER} variant="default" />
                        </div>
                    </div>

                    <div className="space-y-2">
                        <p className="text-xs font-medium tracking-wider text-muted-foreground uppercase">
                            PricingCard
                        </p>
                        <PricingCard
                            name="Starter"
                            price={29}
                            billingPeriod="month"
                            features={MOCK_PRICING_FEATURES}
                            ctaLabel="Get started"
                        />
                    </div>
                </ShowcaseGrid>

                <div className="space-y-2">
                    <p className="text-xs font-medium tracking-wider text-muted-foreground uppercase">
                        ActivityLog
                    </p>
                    <ActivityLog entries={MOCK_ACTIVITIES} />
                </div>
            </div>
        </ShowcaseSection>
    );
}

// ---------------------------------------------------------------------------
// Section: Accessibility
// ---------------------------------------------------------------------------

function AccessibilitySection() {
    return (
        <ShowcaseSection
            id="accessibility"
            title="Accessibility"
            description="WCAG 2.1 AA compliance — keyboard nav, ARIA roles, and colour contrast."
        >
            <ShowcaseRow title="Keyboard Shortcut Display">
                <div className="w-full space-y-2">
                    <p className="text-xs text-muted-foreground">
                        Press{' '}
                        <kbd className="rounded border px-1 font-mono text-[10px]">
                            ?
                        </kbd>{' '}
                        anywhere on this page to open the keyboard shortcuts
                        panel. The{' '}
                        <code className="rounded bg-muted px-1 font-mono text-[11px]">
                            KeyboardShortcutDisplay
                        </code>{' '}
                        component is always mounted on this page.
                    </p>
                </div>
            </ShowcaseRow>

            <ShowcaseRow title="Skip to Content">
                <div className="w-full space-y-2">
                    <p className="text-xs text-muted-foreground">
                        A visually hidden "Skip to content" link is provided via{' '}
                        <code className="rounded bg-muted px-1 font-mono text-[11px]">
                            SkipToContent
                        </code>
                        . Press Tab on page load to reveal it.
                    </p>
                </div>
            </ShowcaseRow>

            <ShowcaseRow title="ARIA & Roles">
                <div className="w-full space-y-2">
                    <p className="text-xs text-muted-foreground">
                        All components include proper ARIA roles, labels, and
                        keyboard navigation. Spinners use{' '}
                        <code className="rounded bg-muted px-1 font-mono text-[11px]">
                            role="status"
                        </code>
                        , progress bars use{' '}
                        <code className="rounded bg-muted px-1 font-mono text-[11px]">
                            role="progressbar"
                        </code>
                        , and dialogs use{' '}
                        <code className="rounded bg-muted px-1 font-mono text-[11px]">
                            role="dialog"
                        </code>
                        .
                    </p>
                </div>
            </ShowcaseRow>

            <ShowcaseRow title="Color Contrast">
                <div className="flex gap-3">
                    {[
                        'text-foreground bg-background',
                        'text-primary-foreground bg-primary',
                        'text-secondary-foreground bg-secondary',
                    ].map((cls) => (
                        <span
                            key={cls}
                            className={cn(
                                'rounded px-3 py-1.5 text-sm font-medium',
                                cls,
                            )}
                        >
                            Aa
                        </span>
                    ))}
                </div>
            </ShowcaseRow>
        </ShowcaseSection>
    );
}

// ---------------------------------------------------------------------------
// Intersection-based active section tracking
// ---------------------------------------------------------------------------

function useActiveSection(sectionIds: string[]): string {
    const [activeId, setActiveId] = React.useState(sectionIds[0] ?? '');

    React.useEffect(() => {
        const observer = new IntersectionObserver(
            (entries) => {
                for (const entry of entries) {
                    if (entry.isIntersecting) {
                        setActiveId(entry.target.id);
                    }
                }
            },
            { rootMargin: '-20% 0px -60% 0px' },
        );

        for (const id of sectionIds) {
            const el = document.getElementById(id);
            if (el) observer.observe(el);
        }

        return () => observer.disconnect();
    }, [sectionIds]);

    return activeId;
}

// ---------------------------------------------------------------------------
// Main page component
// ---------------------------------------------------------------------------

export default function ComponentShowcase() {
    const theme = useActiveTheme();
    const sectionIds = CATEGORIES.map((c) => c.id);
    const activeId = useActiveSection(sectionIds);

    const sidebar = <ShowcaseSidebar activeId={activeId} />;
    const header = <ThemeBar theme={theme} />;

    return (
        <>
            <Head title="Component Showcase" />

            <AppShell sidebar={sidebar} header={header}>
                <div className="mx-auto max-w-5xl space-y-20 p-6">
                    {/* Page hero */}
                    <div className="space-y-4 border-b pb-6">
                        <div className="flex items-center gap-3">
                            <div className="rounded-lg bg-primary/10 p-2">
                                <Code2 className="size-6 text-primary" />
                            </div>
                            <div>
                                <h1 className="text-3xl font-bold tracking-tight">
                                    Component Showcase
                                </h1>
                                <p className="text-muted-foreground">
                                    Interactive previews with copy-ready code
                                    for every component.
                                </p>
                            </div>
                        </div>
                        <div className="flex flex-wrap items-center gap-2">
                            {[
                                '75+ components',
                                '16 categories',
                                'Tailwind CSS v4',
                                'shadcn/ui',
                                'Dark mode',
                            ].map((tag) => (
                                <Badge key={tag} variant="secondary">
                                    {tag}
                                </Badge>
                            ))}
                            <Link
                                href="/dev/pages"
                                className="ml-auto flex items-center gap-1.5 rounded-md border px-3 py-1 text-xs font-medium transition-colors hover:bg-muted"
                            >
                                <LayoutGrid className="size-3.5" />
                                Page Gallery →
                            </Link>
                        </div>
                    </div>

                    <FoundationSection />
                    <LayoutSection />
                    <ShellsSection />
                    <NavigationSection />
                    <ButtonsSection />
                    <FormsSection />
                    <DataDisplaySection />
                    <FeedbackSection />
                    <OverlaySection />
                    <ChartsSection />
                    <MapsSection />
                    <AiSection />
                    <SaasSection />
                    <AdminSection />
                    <ComposedSection />
                    <AccessibilitySection />
                </div>
            </AppShell>

            {/* Always-visible theme customizer (bypasses canCustomize check) */}
            <ThemeCustomizerPanel />

            {/* Keyboard shortcut help — press ? */}
            <KeyboardShortcutDisplay />
        </>
    );
}
