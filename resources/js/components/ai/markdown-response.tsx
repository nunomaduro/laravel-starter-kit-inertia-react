import ReactMarkdown from 'react-markdown';
import remarkGfm from 'remark-gfm';

import { cn } from '@/lib/utils';
import { CodeBlock } from './code-block';

export interface MarkdownResponseProps {
    /** Markdown string to render. */
    content: string;
    className?: string;
}

/**
 * Renders AI markdown responses using react-markdown with GFM support.
 * Code blocks are rendered using the `CodeBlock` component with syntax highlighting.
 */
export function MarkdownResponse({
    content,
    className,
}: MarkdownResponseProps) {
    return (
        <div
            className={cn(
                'prose prose-sm dark:prose-invert max-w-none',
                'prose-p:my-2 prose-headings:mt-4 prose-headings:mb-2',
                'prose-code:rounded prose-code:bg-muted prose-code:px-1 prose-code:py-0.5 prose-code:text-sm prose-code:font-mono',
                'prose-pre:p-0 prose-pre:bg-transparent prose-pre:rounded-lg',
                'prose-table:text-sm prose-th:bg-muted/50',
                className,
            )}
        >
            <ReactMarkdown
                remarkPlugins={[remarkGfm]}
                components={{
                    // Render fenced code blocks with CodeBlock component
                    code({ className: langClass, children, ...props }) {
                        const match = /language-(\w+)/.exec(langClass ?? '');
                        const isBlock = !props.ref && match;

                        if (isBlock) {
                            return (
                                <CodeBlock
                                    code={String(children).replace(/\n$/, '')}
                                    language={match[1]}
                                    className="not-prose my-3"
                                />
                            );
                        }

                        return (
                            <code className={langClass} {...props}>
                                {children}
                            </code>
                        );
                    },
                }}
            >
                {content}
            </ReactMarkdown>
        </div>
    );
}
