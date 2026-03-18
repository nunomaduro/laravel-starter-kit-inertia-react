import {
    KpiCard,
    type KpiCardProps,
} from '@/components/puck-blocks/reports/kpi-card';
import {
    SummaryBlock,
    type SummaryBlockProps,
} from '@/components/puck-blocks/reports/summary-block';
import {
    TableBlock,
    type TableBlockProps,
} from '@/components/puck-blocks/reports/table-block';
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

export function createReportPuckConfig(
    dataSources: DataSourceOption[] = [],
): Config<{
    Heading: HeadingProps;
    Text: TextProps;
    ReportTable: TableBlockProps;
    KpiCard: KpiCardProps;
    Summary: SummaryBlockProps;
}> {
    const dsOptions = dataSources.map((ds) => ({
        label: ds.label,
        value: ds.key,
    }));

    return {
        categories: {
            layout: { title: 'Layout', components: ['Heading', 'Text'] },
            reports: {
                title: 'Reports',
                components: ['ReportTable', 'KpiCard', 'Summary'],
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
                defaultProps: { text: 'Report heading', level: 2 },
                render: HeadingBlock,
            },
            Text: {
                fields: {
                    content: { type: 'textarea', label: 'Content' },
                },
                defaultProps: { content: 'Enter text here.' },
                render: TextBlock,
            },
            ReportTable: {
                fields: {
                    dataSource: {
                        type: 'select',
                        label: 'Data source',
                        options: dsOptions,
                    },
                    title: { type: 'text', label: 'Title' },
                },
                defaultProps: {
                    dataSource: dsOptions[0]?.value ?? '',
                    title: 'Data table',
                    data: undefined,
                },
                render: TableBlock,
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
            Summary: {
                fields: {
                    content: { type: 'textarea', label: 'Summary text' },
                },
                defaultProps: { content: 'Report summary text.' },
                render: SummaryBlock,
            },
        },
        root: {
            render: ({ children }) => (
                <div className="space-y-4">{children}</div>
            ),
        },
    };
}
