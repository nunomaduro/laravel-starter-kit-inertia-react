import PageViewLayout from '@/layouts/page-view-layout';
import { puckConfig } from '@/lib/puck-config';
import { Head } from '@inertiajs/react';
import { Render } from '@measured/puck';
import '@measured/puck/puck.css';

interface PageRecord {
    id: number;
    name: string;
    slug: string;
    puck_json: Record<string, unknown>;
    meta_description?: string | null;
    meta_image?: string | null;
}

interface Props {
    page: PageRecord;
}

export default function PageShow({ page }: Props) {
    const data = (
        page.puck_json?.root !== undefined
            ? page.puck_json
            : { root: {}, content: [] }
    ) as { root: Record<string, unknown>; content: Record<string, unknown>[] };

    const description = page.meta_description ?? undefined;
    const image = page.meta_image ?? undefined;

    return (
        <PageViewLayout>
            <Head title={page.name}>
                {description && (
                    <meta name="description" content={description} />
                )}
                {description && (
                    <meta property="og:description" content={description} />
                )}
                <meta property="og:title" content={page.name} />
                {image && <meta property="og:image" content={image} />}
            </Head>
            <div className="container py-8">
                {/* eslint-disable-next-line @typescript-eslint/no-explicit-any */}
                <Render config={puckConfig} data={data as any} />
            </div>
        </PageViewLayout>
    );
}
