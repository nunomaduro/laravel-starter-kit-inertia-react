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
import {
    baseHeadingComponent,
    baseRootRender,
    baseTextComponent,
    dataSourceOptions,
    type DataSourceOption,
    type HeadingProps,
    type TextProps,
} from '@/lib/puck-config-factory';
import type { Config } from '@measured/puck';

export type { DataSourceOption, HeadingProps, TextProps };

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
    const dsOptions = dataSourceOptions(dataSources);

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
            Heading: baseHeadingComponent('Report heading'),
            Text: baseTextComponent(),
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
        root: baseRootRender(),
    };
}
