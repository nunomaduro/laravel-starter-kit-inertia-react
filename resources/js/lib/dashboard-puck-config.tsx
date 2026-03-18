import {
    ActivityFeedBlock,
    type ActivityFeedBlockProps,
} from '@/components/puck-blocks/dashboards/activity-feed-block';
import {
    KpiGridBlock,
    type KpiGridBlockProps,
} from '@/components/puck-blocks/dashboards/kpi-grid-block';
import {
    LiveChartBlock,
    type LiveChartBlockProps,
} from '@/components/puck-blocks/dashboards/live-chart-block';
import {
    MapBlock,
    type MapBlockProps,
} from '@/components/puck-blocks/dashboards/map-block';
import {
    WidgetBlock,
    type WidgetBlockProps,
} from '@/components/puck-blocks/dashboards/widget-block';
import {
    KpiCard,
    type KpiCardProps,
} from '@/components/puck-blocks/reports/kpi-card';
import type { Config } from '@measured/puck';
import type { ElementType } from 'react';

export interface HeadingProps {
    text: string;
    level: 1 | 2 | 3 | 4 | 5 | 6;
}

export interface TextProps {
    content: string;
}

function HeadingBlock({ text, level: Level }: HeadingProps) {
    const Tag = `h${Level}` as ElementType;
    return <Tag className="font-semibold">{text}</Tag>;
}

function TextBlock({ content }: TextProps) {
    return <p className="text-muted-foreground">{content}</p>;
}

export interface DataSourceOption {
    key: string;
    label: string;
}

export interface StatCardProps {
    label: string;
    value: string;
    description: string;
}

function StatCard({ label, value, description }: StatCardProps) {
    return (
        <div className="rounded-lg border bg-card p-4">
            <p className="text-sm text-muted-foreground">{label}</p>
            <p className="mt-1 text-2xl font-bold">{value}</p>
            {description && (
                <p className="mt-1 text-xs text-muted-foreground">
                    {description}
                </p>
            )}
        </div>
    );
}

export interface QuickLinkProps {
    title: string;
    url: string;
    description: string;
}

function QuickLink({ title, url, description }: QuickLinkProps) {
    return (
        <a
            href={url}
            className="block rounded-lg border bg-card p-4 transition-colors hover:bg-accent"
        >
            <p className="font-medium">{title}</p>
            {description && (
                <p className="mt-1 text-sm text-muted-foreground">
                    {description}
                </p>
            )}
        </a>
    );
}

export interface RecentListProps {
    title: string;
    dataSource: string;
    labelKey: string;
    valueKey: string;
    data?: Record<string, unknown>[];
}

function RecentList({ title, labelKey, valueKey, data }: RecentListProps) {
    const items = data ?? [];
    return (
        <div className="rounded-lg border bg-card p-4">
            <h3 className="mb-3 font-semibold">{title}</h3>
            {items.length === 0 ? (
                <p className="text-sm text-muted-foreground">No data</p>
            ) : (
                <ul className="space-y-2">
                    {items.slice(0, 10).map((item, i) => (
                        <li
                            key={i}
                            className="flex items-center justify-between border-b pb-2 text-sm last:border-0"
                        >
                            <span>{String(item[labelKey] ?? '')}</span>
                            <span className="text-muted-foreground">
                                {String(item[valueKey] ?? '')}
                            </span>
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
}

export function createDashboardPuckConfig(
    dataSources: DataSourceOption[] = [],
    refreshInterval?: number | null,
): Config<{
    Heading: HeadingProps;
    Text: TextProps;
    KpiCard: KpiCardProps;
    StatCard: StatCardProps;
    QuickLink: QuickLinkProps;
    RecentList: RecentListProps;
    LiveChart: LiveChartBlockProps;
    KpiGrid: KpiGridBlockProps;
    ActivityFeed: ActivityFeedBlockProps;
    Map: MapBlockProps;
    Widget: WidgetBlockProps;
}> {
    const dsOptions = dataSources.map((ds) => ({
        label: ds.label,
        value: ds.key,
    }));

    return {
        categories: {
            layout: { title: 'Layout', components: ['Heading', 'Text'] },
            metrics: {
                title: 'Metrics',
                components: ['KpiCard', 'StatCard', 'KpiGrid'],
            },
            charts: {
                title: 'Charts',
                components: ['LiveChart'],
            },
            data: {
                title: 'Data',
                components: ['RecentList', 'ActivityFeed'],
            },
            geo: {
                title: 'Geographic',
                components: ['Map'],
            },
            navigation: {
                title: 'Navigation',
                components: ['QuickLink'],
            },
            embeds: {
                title: 'Embeds',
                components: ['Widget'],
            },
        },
        components: {
            Heading: {
                fields: {
                    text: { type: 'text', label: 'Text' },
                    level: {
                        type: 'select',
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
                defaultProps: { text: 'Dashboard heading', level: 2 },
                render: HeadingBlock,
            },
            Text: {
                fields: {
                    content: { type: 'textarea', label: 'Content' },
                },
                defaultProps: { content: 'Enter text here.' },
                render: TextBlock,
            },
            KpiCard: {
                fields: {
                    label: { type: 'text', label: 'Label' },
                    value: { type: 'text', label: 'Value' },
                    trend: {
                        type: 'select',
                        label: 'Trend',
                        options: [
                            { label: 'Up', value: 'up' },
                            { label: 'Down', value: 'down' },
                            { label: 'Neutral', value: 'neutral' },
                        ],
                    },
                    trendLabel: { type: 'text', label: 'Trend label' },
                },
                defaultProps: {
                    label: 'Metric',
                    value: '0',
                    trend: 'neutral',
                    trendLabel: '',
                },
                render: KpiCard,
            },
            StatCard: {
                fields: {
                    label: { type: 'text', label: 'Label' },
                    value: { type: 'text', label: 'Value' },
                    description: { type: 'text', label: 'Description' },
                },
                defaultProps: {
                    label: 'Statistic',
                    value: '0',
                    description: '',
                },
                render: StatCard,
            },
            QuickLink: {
                fields: {
                    title: { type: 'text', label: 'Title' },
                    url: { type: 'text', label: 'URL' },
                    description: { type: 'text', label: 'Description' },
                },
                defaultProps: {
                    title: 'Link',
                    url: '#',
                    description: '',
                },
                render: QuickLink,
            },
            RecentList: {
                fields: {
                    title: { type: 'text', label: 'Title' },
                    dataSource: {
                        type: 'select',
                        label: 'Data source',
                        options: dsOptions,
                    },
                    labelKey: { type: 'text', label: 'Label key' },
                    valueKey: { type: 'text', label: 'Value key' },
                },
                defaultProps: {
                    title: 'Recent items',
                    dataSource: dsOptions[0]?.value ?? '',
                    labelKey: 'name',
                    valueKey: 'value',
                    data: undefined,
                },
                render: RecentList,
            },
            LiveChart: {
                fields: {
                    title: { type: 'text', label: 'Title' },
                    dataSource: {
                        type: 'select',
                        label: 'Data source',
                        options: dsOptions,
                    },
                    chartType: {
                        type: 'select',
                        label: 'Chart type',
                        options: [
                            { label: 'Bar', value: 'bar' },
                            { label: 'Line', value: 'line' },
                            { label: 'Pie', value: 'pie' },
                        ],
                    },
                    xKey: { type: 'text', label: 'X axis key' },
                    yKey: { type: 'text', label: 'Y axis key' },
                },
                defaultProps: {
                    title: 'Live chart',
                    dataSource: dsOptions[0]?.value ?? '',
                    chartType: 'bar',
                    xKey: 'name',
                    yKey: 'value',
                    refreshInterval: refreshInterval ?? null,
                    data: undefined,
                },
                render: LiveChartBlock,
            },
            KpiGrid: {
                fields: {
                    columns: {
                        type: 'select',
                        label: 'Columns',
                        options: [
                            { label: '2 columns', value: 2 },
                            { label: '3 columns', value: 3 },
                            { label: '4 columns', value: 4 },
                        ],
                    },
                    items: {
                        type: 'custom',
                        render: () => (
                            <p className="text-xs text-muted-foreground">
                                Configure items in JSON via the data field.
                            </p>
                        ),
                    },
                },
                defaultProps: {
                    columns: 3,
                    items: [
                        {
                            label: 'Revenue',
                            value: '$0',
                            trend: 'neutral' as const,
                            trendLabel: '',
                        },
                        {
                            label: 'Users',
                            value: '0',
                            trend: 'neutral' as const,
                            trendLabel: '',
                        },
                        {
                            label: 'Orders',
                            value: '0',
                            trend: 'neutral' as const,
                            trendLabel: '',
                        },
                    ],
                },
                render: KpiGridBlock,
            },
            ActivityFeed: {
                fields: {
                    title: { type: 'text', label: 'Title' },
                    dataSource: {
                        type: 'select',
                        label: 'Data source',
                        options: dsOptions,
                    },
                    maxItems: {
                        type: 'number',
                        label: 'Max items',
                        min: 1,
                        max: 50,
                    },
                },
                defaultProps: {
                    title: 'Recent activity',
                    dataSource: dsOptions[0]?.value ?? '',
                    maxItems: 10,
                    refreshInterval: refreshInterval ?? null,
                    data: undefined,
                },
                render: ActivityFeedBlock,
            },
            Map: {
                fields: {
                    title: { type: 'text', label: 'Title' },
                    dataSource: {
                        type: 'select',
                        label: 'Data source',
                        options: dsOptions,
                    },
                    height: {
                        type: 'number',
                        label: 'Height (px)',
                        min: 200,
                        max: 800,
                    },
                },
                defaultProps: {
                    title: 'Locations',
                    dataSource: dsOptions[0]?.value ?? '',
                    height: 400,
                    data: undefined,
                },
                render: MapBlock,
            },
            Widget: {
                fields: {
                    title: { type: 'text', label: 'Title' },
                    url: { type: 'text', label: 'URL' },
                    height: {
                        type: 'number',
                        label: 'Height (px)',
                        min: 100,
                        max: 1200,
                    },
                    allowFullscreen: {
                        type: 'radio',
                        label: 'Allow fullscreen',
                        options: [
                            { label: 'Yes', value: true },
                            { label: 'No', value: false },
                        ],
                    },
                },
                defaultProps: {
                    title: '',
                    url: '',
                    height: 400,
                    allowFullscreen: false,
                },
                render: WidgetBlock,
            },
        },
        root: {
            render: ({ children }) => (
                <div className="space-y-4">{children}</div>
            ),
        },
    };
}
