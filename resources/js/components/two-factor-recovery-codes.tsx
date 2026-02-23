import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import { regenerateRecoveryCodes } from '@/routes/two-factor';
import { Form } from '@inertiajs/react';
import { Eye, EyeOff, LockKeyhole, RefreshCw } from 'lucide-react';
import { useCallback, useEffect, useRef, useState } from 'react';
import AlertError from './alert-error';

interface TwoFactorRecoveryCodesProps {
    recoveryCodesList: string[];
    fetchRecoveryCodes: () => Promise<void>;
    errors: string[];
}

export default function TwoFactorRecoveryCodes({
    recoveryCodesList,
    fetchRecoveryCodes,
    errors,
}: TwoFactorRecoveryCodesProps) {
    const [codesAreVisible, setCodesAreVisible] = useState<boolean>(false);
    const codesSectionRef = useRef<HTMLDivElement | null>(null);
    const canRegenerateCodes = recoveryCodesList.length > 0 && codesAreVisible;

    const handleOpenChange = useCallback(
        async (open: boolean) => {
            if (open && !recoveryCodesList.length) {
                await fetchRecoveryCodes();
            }

            setCodesAreVisible(open);

            if (open) {
                setTimeout(() => {
                    codesSectionRef.current?.scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest',
                    });
                });
            }
        },
        [recoveryCodesList.length, fetchRecoveryCodes],
    );

    useEffect(() => {
        if (!recoveryCodesList.length) {
            fetchRecoveryCodes();
        }
    }, [recoveryCodesList.length, fetchRecoveryCodes]);

    const RecoveryCodeIconComponent = codesAreVisible ? EyeOff : Eye;

    return (
        <Card>
            <CardHeader>
                <CardTitle className="flex gap-3">
                    <LockKeyhole className="size-4" aria-hidden="true" />
                    2FA Recovery Codes
                </CardTitle>
                <CardDescription>
                    Recovery codes let you regain access if you lose your 2FA
                    device. Store them in a secure password manager.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <Collapsible
                    open={codesAreVisible}
                    onOpenChange={handleOpenChange}
                >
                    <div className="flex flex-col gap-3 select-none sm:flex-row sm:items-center sm:justify-between">
                        <CollapsibleTrigger asChild>
                            <Button className="w-fit">
                                <RecoveryCodeIconComponent
                                    className="size-4"
                                    aria-hidden="true"
                                />
                                {codesAreVisible ? 'Hide' : 'View'} Recovery
                                Codes
                            </Button>
                        </CollapsibleTrigger>

                        {canRegenerateCodes && (
                            <Form
                                {...regenerateRecoveryCodes.form()}
                                options={{ preserveScroll: true }}
                                onSuccess={fetchRecoveryCodes}
                            >
                                {({ processing }) => (
                                    <Button
                                        variant="secondary"
                                        type="submit"
                                        disabled={processing}
                                        aria-describedby="regenerate-warning"
                                    >
                                        <RefreshCw /> Regenerate Codes
                                    </Button>
                                )}
                            </Form>
                        )}
                    </div>

                    <CollapsibleContent>
                        <div className="mt-3 space-y-3">
                            {errors?.length ? (
                                <AlertError errors={errors} />
                            ) : (
                                <>
                                    <div
                                        ref={codesSectionRef}
                                        className="grid gap-1 rounded-lg bg-muted p-4 font-mono text-sm"
                                        role="list"
                                        aria-label="Recovery codes"
                                    >
                                        {recoveryCodesList.length ? (
                                            recoveryCodesList.map((code) => (
                                                <div
                                                    key={code}
                                                    role="listitem"
                                                    className="select-text"
                                                >
                                                    {code}
                                                </div>
                                            ))
                                        ) : (
                                            <div
                                                className="space-y-2"
                                                aria-label="Loading recovery codes"
                                            >
                                                {Array.from(
                                                    { length: 8 },
                                                    (_, index) => (
                                                        <div
                                                            key={index}
                                                            className="h-4 animate-pulse rounded bg-muted-foreground/20"
                                                            aria-hidden="true"
                                                        />
                                                    ),
                                                )}
                                            </div>
                                        )}
                                    </div>

                                    <div className="text-xs text-muted-foreground select-none">
                                        <p id="regenerate-warning">
                                            Each recovery code can be used once
                                            to access your account and will be
                                            removed after use. If you need more,
                                            click{' '}
                                            <span className="font-bold">
                                                Regenerate Codes
                                            </span>{' '}
                                            above.
                                        </p>
                                    </div>
                                </>
                            )}
                        </div>
                    </CollapsibleContent>
                </Collapsible>
            </CardContent>
        </Card>
    );
}
