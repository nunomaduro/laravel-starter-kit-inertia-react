import {
    CardBlock,
    type CardBlockProps,
} from '@/components/puck-blocks/card-block';
import { CtaBlock, type CtaBlockProps } from '@/components/puck-blocks/cta';
import {
    DataListBlock,
    type DataListBlockProps,
} from '@/components/puck-blocks/data-list-block';
import {
    FeaturesBlock,
    type FeatureItem,
    type FeaturesBlockProps,
} from '@/components/puck-blocks/features';
import { HeroBlock, type HeroBlockProps } from '@/components/puck-blocks/hero';
import type { Config } from '@measured/puck';

export interface HeadingProps {
    text: string;
    level: 1 | 2 | 3 | 4 | 5 | 6;
}

export interface TextProps {
    content: string;
}

function HeadingBlock({ text, level: Level }: HeadingProps) {
    const Tag = `h${Level}` as keyof JSX.IntrinsicElements;
    return <Tag className="font-semibold">{text}</Tag>;
}

function TextBlock({ content }: TextProps) {
    return <p className="text-muted-foreground">{content}</p>;
}

export const puckConfig: Config<{
    Heading: HeadingProps;
    Text: TextProps;
    Hero: HeroBlockProps;
    Features: FeaturesBlockProps;
    Cta: CtaBlockProps;
    CardBlock: CardBlockProps;
    DataListBlock: DataListBlockProps;
}> = {
    categories: {
        layout: { title: 'Layout', components: ['Heading', 'Text'] },
        marketing: {
            title: 'Marketing',
            components: ['Hero', 'Features', 'Cta', 'CardBlock'],
        },
        data: { title: 'Data', components: ['DataListBlock'] },
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
            defaultProps: { text: 'Heading', level: 2 },
            render: HeadingBlock,
        },
        Text: {
            fields: {
                content: { type: 'textarea', label: 'Content' },
            },
            defaultProps: { content: 'Enter text here.' },
            render: TextBlock,
        },
        Hero: {
            fields: {
                title: { type: 'text', label: 'Title' },
                subtitle: { type: 'textarea', label: 'Subtitle' },
                primaryCtaLabel: {
                    type: 'text',
                    label: 'Primary button label',
                },
                primaryCtaHref: { type: 'text', label: 'Primary button URL' },
                secondaryCtaLabel: {
                    type: 'text',
                    label: 'Secondary button label',
                },
                secondaryCtaHref: {
                    type: 'text',
                    label: 'Secondary button URL',
                },
            },
            defaultProps: {
                title: 'Hero headline',
                subtitle: 'Supporting copy for the hero section.',
                primaryCtaLabel: 'Get started',
                primaryCtaHref: '#',
                secondaryCtaLabel: '',
                secondaryCtaHref: '',
            },
            render: HeroBlock,
        },
        Features: {
            fields: {
                heading: { type: 'text', label: 'Heading' },
                subheading: { type: 'text', label: 'Subheading' },
                items: {
                    type: 'array',
                    label: 'Features',
                    arrayFields: {
                        title: { type: 'text', label: 'Title' },
                        description: { type: 'textarea', label: 'Description' },
                    },
                    getItemSummary: (item: FeatureItem) =>
                        item?.title ?? 'Feature',
                },
            },
            defaultProps: {
                heading: 'Features',
                subheading: '',
                items: [
                    {
                        title: 'Feature one',
                        description: 'Description for feature one.',
                    },
                    {
                        title: 'Feature two',
                        description: 'Description for feature two.',
                    },
                    {
                        title: 'Feature three',
                        description: 'Description for feature three.',
                    },
                ],
            },
            render: FeaturesBlock,
        },
        Cta: {
            fields: {
                heading: { type: 'text', label: 'Heading' },
                description: { type: 'textarea', label: 'Description' },
                buttonLabel: { type: 'text', label: 'Button label' },
                buttonHref: { type: 'text', label: 'Button URL' },
            },
            defaultProps: {
                heading: 'Ready to get started?',
                description: '',
                buttonLabel: 'Sign up',
                buttonHref: '#',
            },
            render: CtaBlock,
        },
        CardBlock: {
            fields: {
                title: { type: 'text', label: 'Title' },
                description: { type: 'textarea', label: 'Description' },
                footerLabel: { type: 'text', label: 'Footer button label' },
                footerHref: { type: 'text', label: 'Footer button URL' },
            },
            defaultProps: {
                title: 'Card title',
                description: 'Card description text.',
                footerLabel: '',
                footerHref: '',
            },
            render: CardBlock,
        },
        DataListBlock: {
            fields: {
                dataSource: {
                    type: 'select',
                    label: 'Data source',
                    options: [
                        { label: 'Members', value: 'members' },
                        { label: 'Invoices', value: 'invoices' },
                    ],
                },
                title: { type: 'text', label: 'Title' },
            },
            defaultProps: {
                dataSource: 'members',
                title: '',
                data: undefined,
            },
            render: DataListBlock,
        },
    },
    root: {
        render: ({ children }) => <div className="space-y-4">{children}</div>,
    },
};
