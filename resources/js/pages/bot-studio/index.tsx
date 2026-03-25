import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { EmptyState } from '@/components/ui/empty-state';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import {
    Bot,
    Eye,
    Lock,
    MessageCircle,
    Plus,
    Puzzle,
    Sparkles,
    Wrench,
} from 'lucide-react';

interface AgentDefinition {
    id: number;
    slug: string;
    name: string;
    description: string | null;
    avatar_path: string | null;
    system_prompt: string;
    model: string;
    enabled_tools: string[];
    conversation_starters: string[];
    visibility: string;
    conversations_count?: number;
    knowledge_files_count?: number;
}

interface Props {
    agents: {
        data: AgentDefinition[];
    };
    currentCount: number;
    maxCount: number;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Bot Studio', href: '/bot-studio' },
];

export default function BotStudioIndex({
    agents,
    currentCount,
    maxCount,
}: Props) {
    const agentList = agents.data ?? [];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Bot Studio" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4">
                {/* Header */}
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h1 className="font-mono text-2xl font-bold tracking-tight">
                            My Agents
                        </h1>
                        <p className="mt-1 font-sans text-sm text-muted-foreground">
                            {currentCount} of {maxCount} agents used
                        </p>
                    </div>
                    <div className="flex items-center gap-2">
                        <Button variant="outline" size="sm" asChild>
                            <Link href="/bot-studio/templates">
                                <Sparkles className="mr-1.5 size-4" />
                                Browse Templates
                            </Link>
                        </Button>
                        <Button size="sm" asChild>
                            <Link href="/bot-studio/create">
                                <Plus className="mr-1.5 size-4" />
                                Create Agent
                            </Link>
                        </Button>
                    </div>
                </div>

                {/* Content */}
                {agentList.length === 0 ? (
                    <EmptyState
                        icon={<Bot className="size-6" />}
                        title="Create your first agent"
                        description="Build custom AI agents with their own personality, tools, and knowledge base."
                        action={
                            <Button asChild>
                                <Link href="/bot-studio/create">
                                    <Plus className="mr-1.5 size-4" />
                                    Create Agent
                                </Link>
                            </Button>
                        }
                        secondaryAction={
                            <Button variant="outline" asChild>
                                <Link href="/bot-studio/templates">
                                    Browse Templates
                                </Link>
                            </Button>
                        }
                        bordered
                    />
                ) : (
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                        {agentList.map((agent) => (
                            <AgentCard key={agent.id} agent={agent} />
                        ))}

                        {/* Create new card */}
                        <button
                            type="button"
                            onClick={() => router.visit('/bot-studio/create')}
                            className="flex min-h-[200px] flex-col items-center justify-center gap-3 rounded-xl border border-dashed border-border bg-transparent p-6 text-muted-foreground transition-colors duration-200 hover:border-primary/50 hover:text-foreground"
                        >
                            <div className="flex size-10 items-center justify-center rounded-full bg-muted">
                                <Plus className="size-5" />
                            </div>
                            <span className="font-sans text-sm font-medium">
                                Create new agent
                            </span>
                        </button>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}

function AgentCard({ agent }: { agent: AgentDefinition }) {
    const toolCount = agent.enabled_tools?.length ?? 0;
    const knowledgeCount = agent.knowledge_files_count ?? 0;
    const chatCount = agent.conversations_count ?? 0;

    return (
        <Link
            href={`/bot-studio/${agent.slug}/edit`}
            className="group flex flex-col gap-4 rounded-xl border border-border bg-card p-5 transition-colors duration-200 hover:bg-accent/5"
        >
            {/* Top row: avatar + visibility */}
            <div className="flex items-start justify-between">
                <div className="flex size-10 items-center justify-center rounded-lg bg-muted text-lg">
                    {agent.avatar_path ? (
                        <img
                            src={agent.avatar_path}
                            alt={agent.name}
                            className="size-10 rounded-lg object-cover"
                        />
                    ) : (
                        <Bot className="size-5 text-muted-foreground" />
                    )}
                </div>
                <Badge
                    variant="outline"
                    className="text-[11px] font-mono uppercase tracking-wider"
                >
                    {agent.visibility === 'organization' ? (
                        <>
                            <Eye className="mr-1 size-3" />
                            Organization
                        </>
                    ) : (
                        <>
                            <Lock className="mr-1 size-3" />
                            Private
                        </>
                    )}
                </Badge>
            </div>

            {/* Name + description */}
            <div className="flex-1">
                <h3 className="font-mono text-sm font-semibold tracking-tight group-hover:text-primary">
                    {agent.name}
                </h3>
                {agent.description && (
                    <p className="mt-1 line-clamp-2 font-sans text-xs text-muted-foreground">
                        {agent.description}
                    </p>
                )}
            </div>

            {/* Stats row */}
            <div className="flex items-center gap-4 text-xs text-muted-foreground">
                <span className="inline-flex items-center gap-1">
                    <Wrench className="size-3" />
                    {toolCount} tool{toolCount !== 1 ? 's' : ''}
                </span>
                <span className="inline-flex items-center gap-1">
                    <Puzzle className="size-3" />
                    {knowledgeCount} file{knowledgeCount !== 1 ? 's' : ''}
                </span>
                <span className="inline-flex items-center gap-1">
                    <MessageCircle className="size-3" />
                    {chatCount}
                </span>
            </div>
        </Link>
    );
}
