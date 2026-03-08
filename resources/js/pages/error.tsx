import AppLogoIcon from '@/components/app-logo-icon';
import { Button } from '@/components/ui/button';
import { home } from '@/routes';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';

interface ErrorProps {
    status: number;
}

const errorMessages: Record<number, { title: string; description: string }> = {
    401: {
        title: 'Unauthorized',
        description: 'You need to be logged in to access this page.',
    },
    403: {
        title: 'Forbidden',
        description: "You don't have permission to access this page.",
    },
    404: {
        title: 'Page not found',
        description:
            "The page you're looking for doesn't exist or has been moved.",
    },
    419: {
        title: 'Session expired',
        description:
            'Your session has expired. Please refresh the page and try again.',
    },
    429: {
        title: 'Too many requests',
        description:
            "You've made too many requests. Please slow down and try again shortly.",
    },
    500: {
        title: 'Server error',
        description: "Something went wrong on our end. We're working on it.",
    },
    503: {
        title: 'Service unavailable',
        description: "We're down for maintenance. Please check back soon.",
    },
};

export default function Error({ status }: ErrorProps) {
    const { name } = usePage<SharedData>().props;
    const { title, description } = errorMessages[status] ?? {
        title: 'Something went wrong',
        description: 'An unexpected error has occurred. Please try again.',
    };

    return (
        <>
            <Head title={`${status} — ${title}`} />
            <div className="flex min-h-screen flex-col items-center justify-center bg-background px-6 text-foreground">
                <div className="flex w-full max-w-md flex-col items-center gap-8 text-center">
                    <Link href={home()} className="flex items-center gap-2.5">
                        <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-muted ring-1 ring-border">
                            <AppLogoIcon className="size-5 fill-current text-foreground" />
                        </div>
                        <span className="font-semibold">{name}</span>
                    </Link>

                    <div className="space-y-2">
                        <p className="text-8xl font-bold tracking-tighter text-muted-foreground/30">
                            {status}
                        </p>
                        <h1 className="text-2xl font-semibold tracking-tight">
                            {title}
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            {description}
                        </p>
                    </div>

                    <div className="flex gap-3">
                        <Button
                            variant="outline"
                            onClick={() => window.history.back()}
                        >
                            Go back
                        </Button>
                        <Button asChild>
                            <Link href={home()}>Go to Dashboard</Link>
                        </Button>
                    </div>
                </div>
            </div>
        </>
    );
}
