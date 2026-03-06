import TermsAcceptController from '@/actions/App/Http/Controllers/TermsAcceptController';
import AppLogoIcon from '@/components/app-logo-icon';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { home } from '@/routes';
import { Form, Head, Link } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { useCallback, useState } from 'react';

interface PendingVersion {
    id: number;
    title: string;
    slug: string;
    type: string;
    type_label: string;
    effective_at: string;
    summary: string | null;
    body: string;
    body_html: string;
}

interface TermsAcceptProps {
    pendingVersions: PendingVersion[];
    intended: string;
}

export default function TermsAccept({
    pendingVersions,
    intended,
}: TermsAcceptProps) {
    const [acceptedIds, setAcceptedIds] = useState<number[]>([]);

    const toggleVersion = useCallback((id: number) => {
        setAcceptedIds((prev) =>
            prev.includes(id) ? prev.filter((x) => x !== id) : [...prev, id],
        );
    }, []);

    const allAccepted =
        pendingVersions.length > 0 &&
        pendingVersions.every((v) => acceptedIds.includes(v.id));

    return (
        <div className="min-h-svh bg-background">
            <Head title="Accept Terms" />
            <div className="mx-auto max-w-3xl px-4 py-8">
                <div className="mb-6 flex items-center justify-between">
                    <Link
                        href={home()}
                        className="flex items-center gap-2 font-medium text-foreground"
                    >
                        <AppLogoIcon className="size-8 fill-current" />
                        <span className="sr-only">Home</span>
                    </Link>
                </div>

                <div className="rounded-lg border bg-card p-6 shadow-sm">
                    <div className="mb-6">
                        <h1 className="text-xl font-semibold">
                            New terms require your acceptance
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Please read and accept the following to continue.
                        </p>
                    </div>

                    <Form
                        {...TermsAcceptController.store.form()}
                        className="space-y-6"
                    >
                        <input type="hidden" name="intended" value={intended} />
                        {acceptedIds.map((id) => (
                            <input
                                key={id}
                                type="hidden"
                                name="accepted_ids[]"
                                value={id}
                            />
                        ))}
                        {pendingVersions.map((version) => (
                            <div
                                key={version.id}
                                className="rounded-lg border border-border p-4"
                            >
                                <div className="flex items-start gap-3">
                                    <Checkbox
                                        id={`accept-${version.id}`}
                                        checked={acceptedIds.includes(
                                            version.id,
                                        )}
                                        onCheckedChange={() =>
                                            toggleVersion(version.id)
                                        }
                                    />
                                    <div className="min-w-0 flex-1">
                                        <label
                                            htmlFor={`accept-${version.id}`}
                                            className="cursor-pointer font-semibold"
                                        >
                                            {version.title}
                                            <span className="ml-2 text-xs font-normal text-muted-foreground">
                                                ({version.type_label} ·{' '}
                                                {version.effective_at})
                                            </span>
                                        </label>
                                        {version.summary && (
                                            <p className="mt-1 text-sm text-muted-foreground">
                                                {version.summary}
                                            </p>
                                        )}
                                        <div
                                            className="prose prose-sm dark:prose-invert mt-2 max-h-48 overflow-y-auto [&_a]:text-primary"
                                            // eslint-disable-next-line @eslint-react/dom/no-dangerously-set-innerhtml -- server-rendered terms version body
                                            dangerouslySetInnerHTML={{
                                                __html: version.body_html,
                                            }}
                                        />
                                    </div>
                                </div>
                            </div>
                        ))}

                        <div className="flex justify-end border-t border-border pt-6">
                            <Button
                                type="submit"
                                disabled={!allAccepted}
                                className="min-w-[120px]"
                            >
                                I accept
                            </Button>
                        </div>
                    </Form>
                </div>
            </div>
        </div>
    );
}
