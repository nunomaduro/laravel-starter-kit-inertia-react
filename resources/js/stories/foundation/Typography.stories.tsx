import type { Meta, StoryObj } from '@storybook/react';
import React from 'react';

function TypographyDemo() {
    return (
        <div className="p-6 space-y-8 bg-background text-foreground max-w-2xl">
            <div className="space-y-2">
                <p className="text-xs font-mono text-muted-foreground uppercase tracking-wide">Headings</p>
                <h1 className="scroll-m-20 text-4xl font-extrabold tracking-tight">Heading 1</h1>
                <h2 className="scroll-m-20 text-3xl font-semibold tracking-tight">Heading 2</h2>
                <h3 className="scroll-m-20 text-2xl font-semibold tracking-tight">Heading 3</h3>
                <h4 className="scroll-m-20 text-xl font-semibold tracking-tight">Heading 4</h4>
            </div>

            <div className="space-y-2">
                <p className="text-xs font-mono text-muted-foreground uppercase tracking-wide">Body</p>
                <p className="leading-7">
                    The quick brown fox jumps over the lazy dog. This is the default body text style
                    used throughout the application.
                </p>
                <p className="text-sm leading-6 text-muted-foreground">
                    Small muted text — used for captions, descriptions, and supplementary information.
                </p>
                <p className="text-xs leading-5 text-muted-foreground">
                    Extra small — labels, badges, metadata.
                </p>
            </div>

            <div className="space-y-2">
                <p className="text-xs font-mono text-muted-foreground uppercase tracking-wide">Special</p>
                <p className="text-xl font-semibold">Large / Lead</p>
                <blockquote className="mt-2 border-l-2 pl-6 italic text-muted-foreground">
                    &ldquo;A beautiful quote rendered in blockquote style.&rdquo;
                </blockquote>
                <code className="relative rounded bg-muted px-[0.3rem] py-[0.2rem] font-mono text-sm font-semibold">
                    inline code
                </code>
            </div>

            <div className="space-y-2">
                <p className="text-xs font-mono text-muted-foreground uppercase tracking-wide">Links &amp; Emphasis</p>
                <p>
                    Text with a{' '}
                    <a href="#" className="font-medium underline underline-offset-4 hover:text-primary">
                        hyperlink
                    </a>
                    , <strong>bold</strong>, and <em>italic</em> emphasis.
                </p>
            </div>
        </div>
    );
}

const meta: Meta = {
    title: 'Foundation/Typography',
    component: TypographyDemo,
    tags: ['autodocs'],
    parameters: { layout: 'centered' },
};

export default meta;

export const AllStyles: StoryObj = {
    render: () => <TypographyDemo />,
};
