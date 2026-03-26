import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { EmptyState } from '@/components/ui/empty-state';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
import type { PaginatedData } from '@/types/pagination';
import { Head, Link, router } from '@inertiajs/react';
import {
    Bot,
    ChevronLeft,
    ChevronRight,
    Download,
    Search,
    Star,
    Store,
} from 'lucide-react';
import { useCallback, useEffect, useRef, useState } from 'react';

interface Creator {
    id: number;
    name: string;
}

interface MarketplaceAgent {
    id: number;
    slug: string;
    name: string;
    description: string | null;
    avatar_path: string | null;
    category: string | null;
    model: string;
    enabled_tools: string[];
    conversation_starters: string[];
    average_rating: number;
    review_count: number;
    install_count: number;
    creator?: Creator | null;
}

interface Props {
    agents: PaginatedData<MarketplaceAgent>;
    featured: MarketplaceAgent[];
    categories: string[];
    filters: {
        search: string;
        category: string;
        sort: string;
    };
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Bot Studio', href: '/bot-studio' },
    { title: 'Marketplace', href: '/marketplace' },
];

const SORT_OPTIONS = [
    { value: 'popular', label: 'Popular' },
    { value: 'rating', label: 'Highest Rated' },
    { value: 'newest', label: 'Newest' },
] as const;

export default function MarketplaceIndex({
    agents,
    featured,
    categories,
    filters,
}: Props) {
    const [search, setSearch] = useState(filters.search ?? '');
    const debounceRef = useRef<ReturnType<typeof setTimeout>>(null);

    const applyFilters = useCallback(
        (overrides: Partial<typeof filters>) => {
            const merged = { ...filters, ...overrides };
            router.get(
                '/marketplace',
                {
                    ...(merged.search ? { search: merged.search } : {}),
                    ...(merged.category ? { category: merged.category } : {}),
                    ...(merged.sort !== 'popular'
                        ? { sort: merged.sort }
                        : {}),
                },
                { preserveState: true, replace: true },
            );
        },
        [filters],
    );

    function handleSearchChange(value: string) {
        setSearch(value);
        if (debounceRef.current) clearTimeout(debounceRef.current);
        debounceRef.current = setTimeout(() => {
            applyFilters({ search: value });
        }, 300);
    }

    useEffect(() => {
        return () => {
            if (debounceRef.current) clearTimeout(debounceRef.current);
        };
    }, []);

    const agentList = agents.data ?? [];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Marketplace" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4">
                {/* Header */}
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h1 className="font-mono text-2xl font-bold tracking-tight">
                            Marketplace
                        </h1>
                        <p className="mt-1 font-sans text-sm text-muted-foreground">
                            Browse and install agents created by the community
                        </p>
                    </div>
                    <Button variant="outline" size="sm" asChild>
                        <Link href="/bot-studio">
                            <Bot className="mr-1.5 size-4" />
                            My Agents
                        </Link>
                    </Button>
                </div>

                {/* Featured section */}
                {featured.length > 0 && (
                    <div className="flex flex-col gap-3">
                        <h2 className="font-mono text-sm font-semibold uppercase tracking-wider text-muted-foreground">
                            Featured
                        </h2>
                        <div className="flex gap-4 overflow-x-auto pb-2">
                            {featured.map((agent) => (
                                <FeaturedCard key={agent.id} agent={agent} />
                            ))}
                        </div>
                    </div>
                )}

                {/* Filters */}
                <div className="flex flex-wrap items-center gap-3">
                    <div className="relative flex-1 min-w-[200px] max-w-sm">
                        <Search className="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            value={search}
                            onChange={(e) =>
                                handleSearchChange(e.target.value)
                            }
                            placeholder="Search agents..."
                            className="pl-9"
                        />
                    </div>

                    <Select
                        value={filters.category || 'all'}
                        onValueChange={(v) =>
                            applyFilters({
                                category: v === 'all' ? '' : v,
                            })
                        }
                    >
                        <SelectTrigger className="w-[160px]">
                            <SelectValue placeholder="Category" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">
                                All Categories
                            </SelectItem>
                            {categories.map((cat) => (
                                <SelectItem key={cat} value={cat}>
                                    {cat}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>

                    <Select
                        value={filters.sort || 'popular'}
                        onValueChange={(v) => applyFilters({ sort: v })}
                    >
                        <SelectTrigger className="w-[160px]">
                            <SelectValue placeholder="Sort by" />
                        </SelectTrigger>
                        <SelectContent>
                            {SORT_OPTIONS.map((opt) => (
                                <SelectItem key={opt.value} value={opt.value}>
                                    {opt.label}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>

                {/* Agent grid */}
                {agentList.length === 0 ? (
                    <EmptyState
                        icon={<Store className="size-6" />}
                        title="No agents found"
                        description="Try adjusting your search or filters."
                        bordered
                    />
                ) : (
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                        {agentList.map((agent) => (
                            <AgentCard key={agent.id} agent={agent} />
                        ))}
                    </div>
                )}

                {/* Pagination */}
                {agents.last_page > 1 && (
                    <div className="flex items-center justify-center gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            disabled={!agents.prev_page_url}
                            onClick={() =>
                                agents.prev_page_url &&
                                router.get(agents.prev_page_url, {}, { preserveState: true })
                            }
                        >
                            <ChevronLeft className="mr-1 size-4" />
                            Previous
                        </Button>
                        <span className="font-mono text-xs text-muted-foreground">
                            Page {agents.current_page} of {agents.last_page}
                        </span>
                        <Button
                            variant="outline"
                            size="sm"
                            disabled={!agents.next_page_url}
                            onClick={() =>
                                agents.next_page_url &&
                                router.get(agents.next_page_url, {}, { preserveState: true })
                            }
                        >
                            Next
                            <ChevronRight className="ml-1 size-4" />
                        </Button>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}

function FeaturedCard({ agent }: { agent: MarketplaceAgent }) {
    return (
        <Link
            href={`/marketplace/${agent.slug}`}
            className="group flex w-[280px] shrink-0 flex-col gap-3 rounded-xl border border-border bg-card p-4 transition-colors duration-200 hover:bg-accent/5"
        >
            <div className="flex items-start gap-3">
                <div className="flex size-10 items-center justify-center rounded-lg bg-muted">
                    {agent.avatar_path ? (
                        <img
                            src={agent.avatar_path}
                            alt={agent.name}
                            className="size-10 rounded-lg object-cover"
                        />
                    ) : (
                        <Bot className="size-5 text-muted-foreground" />
                    )}
                </div>
                <div className="flex-1 min-w-0">
                    <h3 className="truncate font-mono text-sm font-semibold tracking-tight group-hover:text-primary">
                        {agent.name}
                    </h3>
                    {agent.creator && (
                        <p className="truncate font-sans text-xs text-muted-foreground">
                            by {agent.creator.name}
                        </p>
                    )}
                </div>
            </div>
            {agent.description && (
                <p className="line-clamp-2 font-sans text-xs text-muted-foreground">
                    {agent.description}
                </p>
            )}
            <div className="flex items-center gap-3 text-xs text-muted-foreground">
                <StarRating rating={agent.average_rating} />
                <span className="inline-flex items-center gap-1">
                    <Download className="size-3" />
                    {agent.install_count}
                </span>
            </div>
        </Link>
    );
}

function AgentCard({ agent }: { agent: MarketplaceAgent }) {
    return (
        <Link
            href={`/marketplace/${agent.slug}`}
            className="group flex flex-col gap-4 rounded-xl border border-border bg-card p-5 transition-colors duration-200 hover:bg-accent/5"
        >
            {/* Top row: avatar + category */}
            <div className="flex items-start justify-between">
                <div className="flex size-10 items-center justify-center rounded-lg bg-muted">
                    {agent.avatar_path ? (
                        <img
                            src={agent.avatar_path}
                            alt={agent.name}
                            className="size-10 rounded-lg object-cover"
                        />
                    ) : (
                        <Bot className="size-5 text-muted-foreground" />
                    )}
                </div>
                {agent.category && (
                    <Badge
                        variant="outline"
                        className="text-[11px] font-mono uppercase tracking-wider"
                    >
                        {agent.category}
                    </Badge>
                )}
            </div>

            {/* Name + creator + description */}
            <div className="flex-1">
                <h3 className="font-mono text-sm font-semibold tracking-tight group-hover:text-primary">
                    {agent.name}
                </h3>
                {agent.creator && (
                    <p className="mt-0.5 font-sans text-xs text-muted-foreground">
                        by {agent.creator.name}
                    </p>
                )}
                {agent.description && (
                    <p className="mt-1.5 line-clamp-2 font-sans text-xs text-muted-foreground">
                        {agent.description}
                    </p>
                )}
            </div>

            {/* Stats row */}
            <div className="flex items-center gap-4 text-xs text-muted-foreground">
                <StarRating rating={agent.average_rating} />
                <span className="inline-flex items-center gap-1">
                    <Download className="size-3" />
                    {agent.install_count}
                </span>
            </div>
        </Link>
    );
}

function StarRating({ rating }: { rating: number }) {
    return (
        <span className="inline-flex items-center gap-1">
            <Star className="size-3 fill-amber-400 text-amber-400" />
            <span className="font-mono">
                {rating > 0 ? rating.toFixed(1) : '--'}
            </span>
        </span>
    );
}
