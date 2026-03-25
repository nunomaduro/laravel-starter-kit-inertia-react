import type { RendererProps } from './renderer-registry';
import { registerRenderer } from './renderer-registry';
import Markdown from 'react-markdown';
import remarkGfm from 'remark-gfm';

function TextRenderer({ data }: RendererProps) {
    const content = (data.content as string) ?? '';

    return (
        <div className="prose prose-sm dark:prose-invert max-w-none [&_code]:rounded [&_code]:bg-background/50 [&_code]:px-1.5 [&_code]:py-0.5 [&_code]:text-xs [&_pre]:overflow-x-auto [&_pre]:rounded-lg [&_pre]:bg-background/50 [&_pre]:p-3 [&_pre_code]:bg-transparent [&_pre_code]:p-0">
            <Markdown remarkPlugins={[remarkGfm]}>{content}</Markdown>
        </div>
    );
}

registerRenderer('text', TextRenderer);

export { TextRenderer };
