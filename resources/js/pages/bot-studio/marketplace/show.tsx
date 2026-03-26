import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
import type { PaginatedData } from '@/types/pagination';
import type { SharedData } from '@/types';
import { Head, router, usePage } from '@inertiajs/react';
import {
    Bot,
    Check,
    ChevronLeft,
    ChevronRight,
    Download,
    FileText,
    MessageCircle,
    Star,
    Wrench,
} from 'lucide-react';
import { useState } from 'react';

interface Creator {
    id: number;
    name: string;
}

interface ReviewUser {
    id: number;
    name: string;
}

interface Review {
    id: number;
    user_id: number;
    rating: number;
    review: string | null;
    created_at: string;
    user?: ReviewUser | null;
}

interface AgentDefinition {
    id: number;
    slug: string;
    name: string;
    description: string | null;
    avatar_path: string | null;
    system_prompt: string;
    model: string;
    category: string | null;
    enabled_tools: string[];
    conversation_starters: string[];
    average_rating: number;
    review_count: number;
    install_count: number;
    knowledge_files_count?: number;
    creator?: Creator | null;
    created_by: number;
}

interface Props {
    definition: AgentDefinition;
    reviews: PaginatedData<Review>;
    isInstalled: boolean;
    userReview: Review | null;
}

export default function MarketplaceShow({
    definition,
    reviews,
    isInstalled,
    userReview,
}: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Bot Studio', href: '/bot-studio' },
        { title: 'Marketplace', href: '/marketplace' },
        { title: definition.name, href: `/marketplace/${definition.slug}` },
    ];

    const toolCount = definition.enabled_tools?.length ?? 0;
    const knowledgeCount = definition.knowledge_files_count ?? 0;
    const starterCount = definition.conversation_starters?.filter(
        (s) => s.trim().length > 0,
    ).length ?? 0;

    const { auth } = usePage<SharedData>().props;
    const [installing, setInstalling] = useState(false);

    function handleInstall() {
        setInstalling(true);
        router.post(
            `/marketplace/${definition.slug}/install`,
            {},
            {
                onFinish: () => setInstalling(false),
            },
        );
    }

    // Build rating distribution from reviews
    const ratingDistribution = [5, 4, 3, 2, 1].map((star) => ({
        star,
        count: reviews.data.filter((r) => r.rating === star).length,
    }));
    const totalReviewsOnPage = reviews.data.length;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={definition.name} />
            <div className="flex h-full flex-1 flex-col gap-8 overflow-y-auto p-4 lg:p-8">
                {/* Header */}
                <div className="flex flex-col gap-6 md:flex-row md:items-start md:justify-between">
                    <div className="flex gap-4">
                        <div className="flex size-16 shrink-0 items-center justify-center rounded-xl bg-muted">
                            {definition.avatar_path ? (
                                <img
                                    src={definition.avatar_path}
                                    alt={definition.name}
                                    className="size-16 rounded-xl object-cover"
                                />
                            ) : (
                                <Bot className="size-8 text-muted-foreground" />
                            )}
                        </div>
                        <div className="flex flex-col gap-1">
                            <div className="flex items-center gap-2">
                                <h1 className="font-mono text-2xl font-bold tracking-tight">
                                    {definition.name}
                                </h1>
                                {definition.category && (
                                    <Badge
                                        variant="outline"
                                        className="text-[11px] font-mono uppercase tracking-wider"
                                    >
                                        {definition.category}
                                    </Badge>
                                )}
                            </div>
                            {definition.creator && (
                                <p className="font-sans text-sm text-muted-foreground">
                                    by {definition.creator.name}
                                </p>
                            )}
                            {definition.description && (
                                <p className="mt-1 max-w-xl font-sans text-sm text-muted-foreground">
                                    {definition.description}
                                </p>
                            )}
                            <div className="mt-2 flex items-center gap-4 text-sm text-muted-foreground">
                                <span className="inline-flex items-center gap-1">
                                    <Star className="size-4 fill-amber-400 text-amber-400" />
                                    <span className="font-mono font-medium">
                                        {definition.average_rating > 0
                                            ? definition.average_rating.toFixed(
                                                  1,
                                              )
                                            : '--'}
                                    </span>
                                    <span>
                                        ({definition.review_count} review
                                        {definition.review_count !== 1
                                            ? 's'
                                            : ''}
                                        )
                                    </span>
                                </span>
                                <span className="inline-flex items-center gap-1">
                                    <Download className="size-4" />
                                    <span className="font-mono">
                                        {definition.install_count}
                                    </span>
                                    install
                                    {definition.install_count !== 1 ? 's' : ''}
                                </span>
                            </div>
                        </div>
                    </div>

                    {/* Install button */}
                    <div className="shrink-0">
                        {isInstalled ? (
                            <Badge
                                variant="outline"
                                className="gap-1.5 px-4 py-2 text-sm font-medium text-teal-500"
                            >
                                <Check className="size-4" />
                                Installed
                            </Badge>
                        ) : (
                            <Button
                                size="lg"
                                onClick={handleInstall}
                                disabled={installing}
                            >
                                <Download className="mr-1.5 size-4" />
                                {installing ? 'Installing...' : 'Install Agent'}
                            </Button>
                        )}
                    </div>
                </div>

                {/* Info section */}
                <div className="grid grid-cols-2 gap-4 md:grid-cols-4">
                    <InfoCard
                        label="Model"
                        value={definition.model}
                        icon={<Bot className="size-4" />}
                    />
                    <InfoCard
                        label="Tools"
                        value={`${toolCount} tool${toolCount !== 1 ? 's' : ''}`}
                        icon={<Wrench className="size-4" />}
                    />
                    <InfoCard
                        label="Knowledge Files"
                        value={`${knowledgeCount} file${knowledgeCount !== 1 ? 's' : ''}`}
                        icon={<FileText className="size-4" />}
                    />
                    <InfoCard
                        label="Conversation Starters"
                        value={`${starterCount} starter${starterCount !== 1 ? 's' : ''}`}
                        icon={<MessageCircle className="size-4" />}
                    />
                </div>

                {/* Conversation starters preview */}
                {starterCount > 0 && (
                    <div className="flex flex-col gap-2">
                        <h2 className="font-mono text-sm font-semibold uppercase tracking-wider text-muted-foreground">
                            Conversation Starters
                        </h2>
                        <div className="flex flex-wrap gap-2">
                            {definition.conversation_starters
                                .filter((s) => s.trim().length > 0)
                                .map((starter, i) => (
                                    <span
                                        key={i}
                                        className="rounded-full border border-border px-3 py-1.5 font-sans text-xs text-muted-foreground"
                                    >
                                        {starter}
                                    </span>
                                ))}
                        </div>
                    </div>
                )}

                {/* Reviews section */}
                <div className="flex flex-col gap-6">
                    <h2 className="font-mono text-sm font-semibold uppercase tracking-wider text-muted-foreground">
                        Reviews
                    </h2>

                    {/* Rating breakdown */}
                    {totalReviewsOnPage > 0 && (
                        <div className="flex flex-col gap-2">
                            {ratingDistribution.map(({ star, count }) => (
                                <div
                                    key={star}
                                    className="flex items-center gap-3"
                                >
                                    <span className="w-8 text-right font-mono text-xs text-muted-foreground">
                                        {star}
                                        <Star className="ml-0.5 inline size-3 fill-amber-400 text-amber-400" />
                                    </span>
                                    <div className="h-2 flex-1 overflow-hidden rounded-full bg-muted">
                                        <div
                                            className="h-full rounded-full bg-amber-400 transition-all duration-200"
                                            style={{
                                                width:
                                                    totalReviewsOnPage > 0
                                                        ? `${(count / totalReviewsOnPage) * 100}%`
                                                        : '0%',
                                            }}
                                        />
                                    </div>
                                    <span className="w-6 font-mono text-xs text-muted-foreground">
                                        {count}
                                    </span>
                                </div>
                            ))}
                        </div>
                    )}

                    {/* Write a review form */}
                    {isInstalled &&
                        definition.created_by !== auth.user.id && (
                            <ReviewForm
                                slug={definition.slug}
                                existingReview={userReview}
                            />
                        )}

                    {/* Individual reviews */}
                    {reviews.data.length > 0 ? (
                        <div className="flex flex-col gap-4">
                            {reviews.data.map((review) => (
                                <ReviewCard key={review.id} review={review} />
                            ))}
                        </div>
                    ) : (
                        <p className="font-sans text-sm text-muted-foreground">
                            No reviews yet. Be the first to review this agent.
                        </p>
                    )}

                    {/* Review pagination */}
                    {reviews.last_page > 1 && (
                        <div className="flex items-center justify-center gap-2">
                            <Button
                                variant="outline"
                                size="sm"
                                disabled={!reviews.prev_page_url}
                                onClick={() =>
                                    reviews.prev_page_url &&
                                    router.get(
                                        reviews.prev_page_url,
                                        {},
                                        { preserveState: true },
                                    )
                                }
                            >
                                <ChevronLeft className="mr-1 size-4" />
                                Previous
                            </Button>
                            <span className="font-mono text-xs text-muted-foreground">
                                Page {reviews.current_page} of{' '}
                                {reviews.last_page}
                            </span>
                            <Button
                                variant="outline"
                                size="sm"
                                disabled={!reviews.next_page_url}
                                onClick={() =>
                                    reviews.next_page_url &&
                                    router.get(
                                        reviews.next_page_url,
                                        {},
                                        { preserveState: true },
                                    )
                                }
                            >
                                Next
                                <ChevronRight className="ml-1 size-4" />
                            </Button>
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}

function InfoCard({
    label,
    value,
    icon,
}: {
    label: string;
    value: string;
    icon: React.ReactNode;
}) {
    return (
        <div className="flex flex-col gap-2 rounded-xl border border-border bg-card p-4">
            <div className="flex items-center gap-2 text-muted-foreground">
                {icon}
                <span className="font-sans text-xs uppercase tracking-wider">
                    {label}
                </span>
            </div>
            <span className="font-mono text-sm font-medium">{value}</span>
        </div>
    );
}

function ReviewForm({
    slug,
    existingReview,
}: {
    slug: string;
    existingReview: Review | null;
}) {
    const [rating, setRating] = useState(existingReview?.rating ?? 0);
    const [reviewText, setReviewText] = useState(
        existingReview?.review ?? '',
    );
    const [hoveredStar, setHoveredStar] = useState(0);
    const [submitting, setSubmitting] = useState(false);

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        if (rating === 0) return;

        setSubmitting(true);
        router.post(
            `/marketplace/${slug}/review`,
            {
                rating,
                review: reviewText,
            },
            {
                preserveScroll: true,
                onFinish: () => setSubmitting(false),
            },
        );
    }

    function handleDelete() {
        if (!confirm('Delete your review?')) return;
        router.delete(`/marketplace/${slug}/review`, {
            preserveScroll: true,
        });
    }

    return (
        <form
            onSubmit={handleSubmit}
            className="flex flex-col gap-4 rounded-xl border border-border bg-card p-4"
        >
            <h3 className="font-mono text-sm font-semibold tracking-tight">
                {existingReview ? 'Update Your Review' : 'Write a Review'}
            </h3>

            {/* Star selector */}
            <div className="flex items-center gap-1">
                {[1, 2, 3, 4, 5].map((star) => (
                    <button
                        key={star}
                        type="button"
                        onClick={() => setRating(star)}
                        onMouseEnter={() => setHoveredStar(star)}
                        onMouseLeave={() => setHoveredStar(0)}
                        className="p-0.5 transition-colors duration-100"
                    >
                        <Star
                            className={`size-5 ${
                                star <= (hoveredStar || rating)
                                    ? 'fill-amber-400 text-amber-400'
                                    : 'text-muted-foreground/30'
                            }`}
                        />
                    </button>
                ))}
                {rating > 0 && (
                    <span className="ml-2 font-mono text-xs text-muted-foreground">
                        {rating}/5
                    </span>
                )}
            </div>

            <Textarea
                value={reviewText}
                onChange={(e) => setReviewText(e.target.value)}
                placeholder="Share your experience with this agent..."
                minRows={3}
                maxRows={6}
                autoSize
                className="text-sm"
            />

            <div className="flex items-center gap-2">
                <Button
                    type="submit"
                    size="sm"
                    disabled={rating === 0 || submitting}
                >
                    {submitting
                        ? 'Submitting...'
                        : existingReview
                          ? 'Update Review'
                          : 'Submit Review'}
                </Button>
                {existingReview && (
                    <Button
                        type="button"
                        variant="ghost"
                        size="sm"
                        onClick={handleDelete}
                        className="text-destructive"
                    >
                        Delete Review
                    </Button>
                )}
            </div>
        </form>
    );
}

function ReviewCard({ review }: { review: Review }) {
    const date = new Date(review.created_at);
    const formattedDate = date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });

    return (
        <div className="flex flex-col gap-2 rounded-xl border border-border bg-card p-4">
            <div className="flex items-center justify-between">
                <div className="flex items-center gap-2">
                    <span className="font-sans text-sm font-medium">
                        {review.user?.name ?? 'Anonymous'}
                    </span>
                    <div className="flex items-center gap-0.5">
                        {[1, 2, 3, 4, 5].map((star) => (
                            <Star
                                key={star}
                                className={`size-3 ${
                                    star <= review.rating
                                        ? 'fill-amber-400 text-amber-400'
                                        : 'text-muted-foreground/30'
                                }`}
                            />
                        ))}
                    </div>
                </div>
                <span className="font-mono text-xs text-muted-foreground">
                    {formattedDate}
                </span>
            </div>
            {review.review && (
                <p className="font-sans text-sm text-muted-foreground">
                    {review.review}
                </p>
            )}
        </div>
    );
}
