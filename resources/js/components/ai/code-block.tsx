import * as React from 'react';
import { Prism as SyntaxHighlighter } from 'react-syntax-highlighter';
import { oneDark } from 'react-syntax-highlighter/dist/cjs/styles/prism';
import { CheckIcon, CopyIcon } from 'lucide-react';

import { cn } from '@/lib/utils';
import { Button } from '@/components/ui/button';

export interface CodeBlockProps {
    /** The source code to display. */
    code: string;
    /** Language identifier for syntax highlighting (e.g. "typescript", "python"). */
    language?: string;
    /** Optional filename to show in the header. */
    filename?: string;
    /** Show a copy button. */
    showCopy?: boolean;
    className?: string;
}

/**
 * Syntax-highlighted code block using react-syntax-highlighter (Prism).
 * Includes an optional copy-to-clipboard button.
 */
export function CodeBlock({
    code,
    language = 'text',
    filename,
    showCopy = true,
    className,
}: CodeBlockProps) {
    const [copied, setCopied] = React.useState(false);

    const handleCopy = React.useCallback(async () => {
        await navigator.clipboard.writeText(code);
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
    }, [code]);

    return (
        <div className={cn('group relative rounded-lg border bg-[#282c34] overflow-hidden', className)}>
            {/* Header */}
            {(filename ?? showCopy) && (
                <div className="flex items-center justify-between border-b border-white/10 px-3 py-1.5">
                    <span className="text-xs text-white/60 font-mono">
                        {filename ?? language}
                    </span>
                    {showCopy && (
                        <Button
                            variant="ghost"
                            size="icon-xs"
                            className="text-white/60 hover:text-white hover:bg-white/10"
                            onClick={handleCopy}
                            aria-label={copied ? 'Copied' : 'Copy code'}
                        >
                            {copied ? (
                                <CheckIcon className="size-3" />
                            ) : (
                                <CopyIcon className="size-3" />
                            )}
                        </Button>
                    )}
                </div>
            )}

            <SyntaxHighlighter
                language={language}
                style={oneDark}
                customStyle={{
                    margin: 0,
                    borderRadius: 0,
                    background: 'transparent',
                    padding: '1rem',
                    fontSize: '0.8125rem',
                    lineHeight: '1.6',
                }}
                wrapLongLines
            >
                {code}
            </SyntaxHighlighter>
        </div>
    );
}
