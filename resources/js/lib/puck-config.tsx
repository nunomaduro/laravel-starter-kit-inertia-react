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
import {
    baseHeadingComponent,
    baseRootRender,
    baseTextComponent,
    type HeadingProps,
    type TextProps,
} from '@/lib/puck-config-factory';
import type { Config } from '@measured/puck';

export type { HeadingProps, TextProps };

export function createPagePuckConfig(): Config<{
    Heading: HeadingProps;
    Text: TextProps;
    Hero: HeroBlockProps;
    Features: FeaturesBlockProps;
    Cta: CtaBlockProps;
    CardBlock: CardBlockProps;
    DataListBlock: DataListBlockProps;
}> {
    return {
        categories: {
            layout: { title: 'Layout', components: ['Heading', 'Text'] },
            marketing: {
                title: 'Marketing',
                components: ['Hero', 'Features', 'Cta', 'CardBlock'],
            },
            data: { title: 'Data', components: ['DataListBlock'] },
        },
        components: {
            Heading: baseHeadingComponent('Heading'),
            Text: baseTextComponent(),
            Hero: {
                fields: {
                    title: { type: 'text', label: 'Title' },
                    subtitle: { type: 'textarea', label: 'Subtitle' },
                    primaryCtaLabel: {
                        type: 'text',
                        label: 'Primary button label',
                    },
                    primaryCtaHref: {
                        type: 'text',
                        label: 'Primary button URL',
                    },
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
                            description: {
                                type: 'textarea',
                                label: 'Description',
                            },
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
                    footerLabel: {
                        type: 'text',
                        label: 'Footer button label',
                    },
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
        root: baseRootRender(),
    };
}

/** @deprecated Use createPagePuckConfig() instead */
export const puckConfig = createPagePuckConfig();
