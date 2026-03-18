export interface SummaryBlockProps {
    content: string;
}

export function SummaryBlock({ content }: SummaryBlockProps) {
    return (
        <div className="rounded-lg border bg-card p-4">
            <p className="text-sm leading-relaxed text-foreground">{content}</p>
        </div>
    );
}
