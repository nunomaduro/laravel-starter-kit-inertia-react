import {
    ChartBlock,
    type ChartBlockProps,
} from '@/components/puck-blocks/reports/chart-block';
import {
    FilterBlock,
    type FilterBlockProps,
    type FilterOption,
} from '@/components/puck-blocks/reports/filter-block';
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
    Chart: ChartBlockProps;
    KpiCard: KpiCardProps;
    Filter: FilterBlockProps;
    Summary: SummaryBlockProps;
}> {
    const dsOptions = dataSources.map((ds) => ({
        label: ds.label,
        value: ds.key,
    }));

    return {
        categories: {
            layout: { title: 'Layout', components: ['Heading', 'Text'] },
            data: {
                title: 'Data',
                components: ['ReportTable', 'Chart', 'KpiCard'],
            },
            controls: {
                title: 'Controls',
                components: ['Filter'],
            },
            content: {
                title: 'Content',
                components: ['Summary'],
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
            Chart: {
                fields: {
                    dataSource: {
                        type: 'select',
                        label: 'Data source',
                        options: dsOptions,
                    },
                    title: { type: 'text', label: 'Title' },
                    chartType: {
                        type: 'select',
                        label: 'Chart type',
                        options: [
                            { label: 'Bar', value: 'bar' },
                            { label: 'Line', value: 'line' },
                            { label: 'Pie', value: 'pie' },
                        ],
                    },
                    xKey: { type: 'text', label: 'X-axis key' },
                    yKey: { type: 'text', label: 'Y-axis key' },
                },
                defaultProps: {
                    dataSource: dsOptions[0]?.value ?? '',
                    title: 'Chart',
                    chartType: 'bar',
                    xKey: 'name',
                    yKey: 'value',
                    data: undefined,
                },
                render: ChartBlock,
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
            Filter: {
                fields: {
                    label: { type: 'text', label: 'Label' },
                    filterType: {
                        type: 'select',
                        label: 'Filter type',
                        options: [
                            { label: 'Date range', value: 'date_range' },
                            { label: 'Dropdown', value: 'dropdown' },
                        ],
                    },
                    parameterName: { type: 'text', label: 'Parameter name' },
                    options: {
                        type: 'array',
                        label: 'Dropdown options',
                        arrayFields: {
                            label: { type: 'text', label: 'Label' },
                            value: { type: 'text', label: 'Value' },
                        },
                        getItemSummary: (item: FilterOption) =>
                            item?.label ?? 'Option',
                    },
                    defaultFrom: { type: 'text', label: 'Default from date' },
                    defaultTo: { type: 'text', label: 'Default to date' },
                    defaultValue: {
                        type: 'text',
                        label: 'Default dropdown value',
                    },
                },
                defaultProps: {
                    label: 'Filter',
                    filterType: 'dropdown',
                    parameterName: 'filter',
                    options: [],
                    defaultFrom: '',
                    defaultTo: '',
                    defaultValue: '',
                },
                render: FilterBlock,
            },
            Summary: {
                fields: {
                    dataSource: {
                        type: 'select',
                        label: 'Data source',
                        options: dsOptions,
                    },
                    content: {
                        type: 'textarea',
                        label: 'Summary text (use {{key}} for variables)',
                    },
                },
                defaultProps: {
                    content: 'Report summary text.',
                    dataSource: '',
                    data: undefined,
                },
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
