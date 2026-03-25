import { Button } from '@/components/ui/button';
import { EmptyState } from '@/components/ui/empty-state';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { ArrowLeft, Bot, Copy, Cpu, Wrench } from 'lucide-react';
import { useState } from 'react';

interface AgentDefinition {
    id: number;
    slug: string;
    name: string;
    description: string | null;
    avatar_path: string | null;
    model: string;
    enabled_tools: string[];
    conversation_starters: string[];
}

interface Props {
    templates: {
        data: AgentDefinition[];
    };
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Bot Studio', href: '/bot-studio' },
    { title: 'Templates', href: '/bot-studio/templates' },
];

export default function BotStudioTemplates({ templates }: Props) {
    const templateList = templates.data ?? [];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Agent Templates" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4">
                {/* Header */}
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h1 className="font-mono text-2xl font-bold tracking-tight">
                            Templates
                        </h1>
                        <p className="mt-1 font-sans text-sm text-muted-foreground">
                            Start with a pre-built agent and customize it
                            to your needs.
                        </p>
                    </div>
                    <Button variant="outline" size="sm" asChild>
                        <a href="/bot-studio">
                            <ArrowLeft className="mr-1.5 size-4" />
                            Back to My Agents
                        </a>
                    </Button>
                </div>

                {/* Content */}
                {templateList.length === 0 ? (
                    <EmptyState
                        icon={<Bot className="size-6" />}
                        title="No templates available"
                        description="Templates will appear here when modules or administrators publish them."
                        bordered
                    />
                ) : (
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                        {templateList.map((template) => (
                            <TemplateCard
                                key={template.id}
                                template={template}
                            />
                        ))}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}

function TemplateCard({ template }: { template: AgentDefinition }) {
    const [duplicating, setDuplicating] = useState(false);

    function handleUseTemplate() {
        setDuplicating(true);
        router.post(
            `/bot-studio/${template.slug}/duplicate`,
            {},
            {
                onFinish: () => setDuplicating(false),
            },
        );
    }

    const toolCount = template.enabled_tools?.length ?? 0;

    return (
        <div className="flex flex-col gap-4 rounded-xl border border-border bg-card p-5">
            {/* Top row */}
            <div className="flex items-start gap-3">
                <div className="flex size-10 items-center justify-center rounded-lg bg-muted">
                    {template.avatar_path ? (
                        <img
                            src={template.avatar_path}
                            alt={template.name}
                            className="size-10 rounded-lg object-cover"
                        />
                    ) : (
                        <Bot className="size-5 text-muted-foreground" />
                    )}
                </div>
                <div className="flex-1">
                    <h3 className="font-mono text-sm font-semibold tracking-tight">
                        {template.name}
                    </h3>
                    {template.description && (
                        <p className="mt-1 line-clamp-2 font-sans text-xs text-muted-foreground">
                            {template.description}
                        </p>
                    )}
                </div>
            </div>

            {/* Meta */}
            <div className="flex items-center gap-3 text-xs text-muted-foreground">
                <span className="inline-flex items-center gap-1">
                    <Cpu className="size-3" />
                    {template.model}
                </span>
                {toolCount > 0 && (
                    <span className="inline-flex items-center gap-1">
                        <Wrench className="size-3" />
                        {toolCount} tool{toolCount !== 1 ? 's' : ''}
                    </span>
                )}
            </div>

            {/* Action */}
            <Button
                variant="outline"
                size="sm"
                onClick={handleUseTemplate}
                disabled={duplicating}
                className="w-full"
            >
                <Copy className="mr-1.5 size-3.5" />
                {duplicating ? 'Creating...' : 'Use this template'}
            </Button>
        </div>
    );
}
