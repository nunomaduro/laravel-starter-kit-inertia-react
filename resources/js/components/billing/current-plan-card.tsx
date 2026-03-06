import { CreditCard } from 'lucide-react';

import { Button } from '@/components/ui/button';
import {
    Card,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Link } from '@inertiajs/react';

interface Props {
    activePlan: { id: number; name: string } | null;
    isOnTrial: boolean;
}

export default function CurrentPlanCard({ activePlan, isOnTrial }: Props) {
    return (
        <Card>
            <CardHeader>
                <CardTitle className="flex items-center gap-2">
                    <CreditCard className="size-5" />
                    Current plan
                </CardTitle>
                <CardDescription>
                    {activePlan ? activePlan.name : 'No active subscription'}
                    {isOnTrial && ' (Trial)'}
                </CardDescription>
            </CardHeader>
            {!activePlan && !isOnTrial && (
                <CardFooter>
                    <Button variant="outline" size="sm" asChild>
                        <Link href="/pricing">View plans</Link>
                    </Button>
                </CardFooter>
            )}
        </Card>
    );
}
