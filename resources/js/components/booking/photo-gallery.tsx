import { ChevronLeft, ChevronRight } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogTitle } from '@/components/ui/dialog';
import type { PropertyImage } from '@/types';

type PhotoGalleryProps = {
    images: PropertyImage[];
};

export function PhotoGallery({ images }: PhotoGalleryProps) {
    const [lightboxOpen, setLightboxOpen] = useState(false);
    const [currentIndex, setCurrentIndex] = useState(0);

    const sortedImages = [...images].sort((a, b) => a.order - b.order);

    if (sortedImages.length === 0) {
        return (
            <div className="flex aspect-[21/9] items-center justify-center rounded-xl bg-neutral-100 dark:bg-neutral-800">
                <span className="text-muted-foreground">No photos available</span>
            </div>
        );
    }

    const openLightbox = (index: number) => {
        setCurrentIndex(index);
        setLightboxOpen(true);
    };

    const goToPrev = () => {
        setCurrentIndex((prev) => (prev === 0 ? sortedImages.length - 1 : prev - 1));
    };

    const goToNext = () => {
        setCurrentIndex((prev) => (prev === sortedImages.length - 1 ? 0 : prev + 1));
    };

    const mainImage = sortedImages[0];
    const thumbnails = sortedImages.slice(1, 5);

    return (
        <>
            <div className="grid grid-cols-1 gap-2 md:grid-cols-4 md:grid-rows-2">
                <button
                    type="button"
                    className="relative col-span-1 overflow-hidden rounded-xl md:col-span-2 md:row-span-2"
                    onClick={() => openLightbox(0)}
                >
                    <img
                        src={mainImage.path}
                        alt="Main property photo"
                        className="aspect-[4/3] size-full object-cover transition-transform hover:scale-105 md:aspect-auto md:h-full"
                    />
                </button>
                {thumbnails.map((image, i) => (
                    <button
                        key={image.id}
                        type="button"
                        className="relative hidden overflow-hidden rounded-xl md:block"
                        onClick={() => openLightbox(i + 1)}
                    >
                        <img
                            src={image.path}
                            alt={`Property photo ${i + 2}`}
                            className="aspect-[4/3] size-full object-cover transition-transform hover:scale-105"
                        />
                        {i === 3 && sortedImages.length > 5 && (
                            <div className="absolute inset-0 flex items-center justify-center bg-black/50">
                                <span className="text-lg font-semibold text-white">+{sortedImages.length - 5} more</span>
                            </div>
                        )}
                    </button>
                ))}
            </div>

            <Dialog open={lightboxOpen} onOpenChange={setLightboxOpen}>
                <DialogContent className="max-w-4xl border-none bg-black/95 p-0 sm:max-w-4xl">
                    <DialogTitle className="sr-only">Photo {currentIndex + 1} of {sortedImages.length}</DialogTitle>
                    <div className="relative flex items-center justify-center">
                        <img
                            src={sortedImages[currentIndex].path}
                            alt={`Property photo ${currentIndex + 1}`}
                            className="max-h-[80vh] w-full object-contain"
                        />
                        {sortedImages.length > 1 && (
                            <>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    className="absolute left-2 text-white hover:bg-white/20"
                                    onClick={goToPrev}
                                >
                                    <ChevronLeft className="size-6" />
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    className="absolute right-2 text-white hover:bg-white/20"
                                    onClick={goToNext}
                                >
                                    <ChevronRight className="size-6" />
                                </Button>
                            </>
                        )}
                    </div>
                    <div className="pb-4 text-center text-sm text-neutral-400">
                        {currentIndex + 1} / {sortedImages.length}
                    </div>
                </DialogContent>
            </Dialog>
        </>
    );
}
