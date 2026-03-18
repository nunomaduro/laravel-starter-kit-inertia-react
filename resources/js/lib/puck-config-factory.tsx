import type { Config } from '@measured/puck';
import type { ElementType } from 'react';

export type BuilderType = 'page' | 'report' | 'dashboard';

export interface DataSourceOption {
    key: string;
    label: string;
}

export interface HeadingProps {
    text: string;
    level: 1 | 2 | 3 | 4 | 5 | 6;
}

export interface TextProps {
    content: string;
}

export interface PuckConfigFactoryOptions {
    dataSources?: DataSourceOption[];
    refreshInterval?: number | null;
}

export function HeadingBlock({ text, level: Level }: HeadingProps) {
    const Tag = `h${Level}` as ElementType;
    return <Tag className="font-semibold">{text}</Tag>;
}

export function TextBlock({ content }: TextProps) {
    return <p className="text-muted-foreground">{content}</p>;
}

export function baseHeadingComponent(defaultText: string = 'Heading') {
    return {
        fields: {
            text: { type: 'text' as const, label: 'Text' },
            level: {
                type: 'select' as const,
                label: 'Level',
                options: [
                    { label: 'H1', value: 1 },
                    { label: 'H2', value: 2 },
                    { label: 'H3', value: 3 },
                    { label: 'H4', value: 4 },
                    { label: 'H5', value: 5 },
                    { label: 'H6', value: 6 },
                ],
            },
        },
        defaultProps: { text: defaultText, level: 2 as const },
        render: HeadingBlock,
    };
}

export function baseTextComponent() {
    return {
        fields: {
            content: { type: 'textarea' as const, label: 'Content' },
        },
        defaultProps: { content: 'Enter text here.' },
        render: TextBlock,
    };
}

export function dataSourceOptions(dataSources: DataSourceOption[]) {
    return dataSources.map((ds) => ({
        label: ds.label,
        value: ds.key,
    }));
}

export function baseRootRender() {
    return {
        render: ({ children }: { children: React.ReactNode }) => (
            <div className="space-y-4">{children}</div>
        ),
    };
}

/**
 * Creates a Puck config for the given builder type.
 *
 * Each builder shares base blocks (Heading, Text) and adds builder-specific blocks.
 * - page: marketing blocks (Hero, Features, Cta, CardBlock, DataListBlock)
 * - report: data blocks (ReportTable, Chart, KpiCard, Filter, Summary)
 * - dashboard: real-time blocks (LiveChart, KpiGrid, ActivityFeed, Map, Widget, etc.)
 */
export async function createPuckConfig(
    builderType: BuilderType,
    options: PuckConfigFactoryOptions = {},
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
): Promise<Config<any>> {
    switch (builderType) {
        case 'page': {
            const { createPagePuckConfig } = await import(
                '@/lib/puck-config'
            );
            return createPagePuckConfig();
        }
        case 'report': {
            const { createReportPuckConfig } = await import(
                '@/lib/report-puck-config'
            );
            return createReportPuckConfig(options.dataSources ?? []);
        }
        case 'dashboard': {
            const { createDashboardPuckConfig } = await import(
                '@/lib/dashboard-puck-config'
            );
            return createDashboardPuckConfig(
                options.dataSources ?? [],
                options.refreshInterval,
            );
        }
    }
}
