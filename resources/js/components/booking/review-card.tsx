import { StarRating } from '@/components/booking/star-rating';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/hooks/use-initials';
import type { Review } from '@/types';

type ReviewCardProps = {
    review: Review;
};

export function ReviewCard({ review }: ReviewCardProps) {
    const getInitials = useInitials();

    return (
        <div className="space-y-3">
            <div className="flex items-start gap-3">
                <Avatar className="size-10">
                    <AvatarImage src={review.guest.avatar ?? undefined} alt={review.guest.name} />
                    <AvatarFallback className="bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                        {getInitials(review.guest.name)}
                    </AvatarFallback>
                </Avatar>
                <div className="flex-1">
                    <div className="flex items-center gap-2">
                        <span className="font-medium">{review.guest.name}</span>
                        <StarRating rating={review.rating} size="sm" />
                    </div>
                    <time className="text-xs text-muted-foreground">
                        {new Date(review.created_at).toLocaleDateString()}
                    </time>
                    <p className="mt-2 text-sm leading-relaxed">{review.comment}</p>
                </div>
            </div>

            {review.host_response && (
                <div className="ml-13 rounded-lg bg-neutral-50 p-3 dark:bg-neutral-900">
                    <p className="text-xs font-medium text-muted-foreground">Host response</p>
                    <p className="mt-1 text-sm leading-relaxed">{review.host_response}</p>
                    {review.host_responded_at && (
                        <time className="mt-1 block text-xs text-muted-foreground">
                            {new Date(review.host_responded_at).toLocaleDateString()}
                        </time>
                    )}
                </div>
            )}
        </div>
    );
}
