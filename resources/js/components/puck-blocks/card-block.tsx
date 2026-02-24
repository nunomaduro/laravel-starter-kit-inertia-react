import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

export interface CardBlockProps {
    title: string;
    description: string;
    footerLabel?: string;
    footerHref?: string;
}

export function CardBlock({
    title,
    description,
    footerLabel,
    footerHref,
}: CardBlockProps) {
    return (
        <Card>
            <CardHeader>
                <CardTitle>{title}</CardTitle>
                <CardDescription>{description}</CardDescription>
            </CardHeader>
            <CardContent />
            {footerLabel && footerHref && (
                <CardFooter>
                    <Button asChild variant="outline" size="sm">
                        <a href={footerHref}>{footerLabel}</a>
                    </Button>
                </CardFooter>
            )}
        </Card>
    );
}
