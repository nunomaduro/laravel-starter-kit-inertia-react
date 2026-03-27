import { Star } from 'lucide-react';
import { useState } from 'react';
import { cn } from '@/lib/utils';

type StarRatingProps = {
    rating: number;
    max?: number;
    size?: 'sm' | 'md' | 'lg';
    interactive?: boolean;
    onChange?: (rating: number) => void;
};

const sizeClasses = {
    sm: 'size-3.5',
    md: 'size-4',
    lg: 'size-5',
};

export function StarRating({ rating, max = 5, size = 'md', interactive = false, onChange }: StarRatingProps) {
    const [hovered, setHovered] = useState(0);

    const displayRating = interactive && hovered > 0 ? hovered : rating;

    return (
        <div
            className={cn('flex items-center gap-0.5', interactive && 'cursor-pointer')}
            onMouseLeave={() => interactive && setHovered(0)}
        >
            {Array.from({ length: max }, (_, i) => {
                const starIndex = i + 1;
                const isFilled = starIndex <= displayRating;

                return (
                    <Star
                        key={i}
                        className={cn(
                            sizeClasses[size],
                            'transition-colors',
                            isFilled
                                ? 'fill-amber-400 text-amber-400'
                                : 'fill-transparent text-neutral-300 dark:text-neutral-600',
                        )}
                        onMouseEnter={() => interactive && setHovered(starIndex)}
                        onClick={() => interactive && onChange?.(starIndex)}
                    />
                );
            })}
        </div>
    );
}
