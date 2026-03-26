import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Slider } from '@/components/ui/slider';
import { Switch } from '@/components/ui/switch';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import {
    AlertCircle,
    Bot,
    Check,
    FileText,
    Globe,
    Loader2,
    Lock,
    MessageCircle,
    Plus,
    RefreshCw,
    Send,
    Settings2,
    Sparkles,
    Trash2,
    Upload,
    Wrench,
    X,
} from 'lucide-react';
import { useCallback, useEffect, useRef, useState } from 'react';

interface Tool {
    class: string;
    name: string;
    description?: string;
    plan_required?: string | null;
}

interface KnowledgeFile {
    id: number;
    filename: string;
    mime_type: string | null;
    file_size: number | null;
    status: string;
    chunk_count: number;
    error_message: string | null;
    processed_at: string | null;
}

interface AgentDefinition {
    id: number;
    slug: string;
    name: string;
    description: string | null;
    avatar_path: string | null;
    system_prompt: string;
    model: string;
    temperature: number;
    max_tokens: number;
    enabled_tools: string[];
    conversation_starters: string[];
    visibility: string;
    is_published: boolean;
    category: string | null;
    wizard_answers: Record<string, string> | null;
    knowledge_files?: KnowledgeFile[];
}

interface Props {
    definition: AgentDefinition;
    availableTools: Tool[];
    allowedModels: string[];
}

interface ChatMessage {
    id: string;
    role: 'user' | 'assistant';
    content: string;
}

const VARIABLE_BUTTONS = [
    { label: '{{org_name}}', value: '{{org_name}}' },
    { label: '{{user_name}}', value: '{{user_name}}' },
    { label: '{{current_date}}', value: '{{current_date}}' },
];

export default function BotStudioEdit({
    definition,
    availableTools,
    allowedModels,
}: Props) {
    const [saving, setSaving] = useState(false);
    const [form, setForm] = useState({
        system_prompt: definition.system_prompt ?? '',
        model: definition.model,
        temperature: Number(definition.temperature),
        max_tokens: definition.max_tokens ?? 4096,
        enabled_tools: definition.enabled_tools ?? [],
        conversation_starters: definition.conversation_starters?.length
            ? definition.conversation_starters
            : [''],
        visibility: definition.visibility ?? 'private',
        is_published: definition.is_published ?? false,
        category: definition.category ?? '',
    });

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Bot Studio', href: '/bot-studio' },
        {
            title: definition.name,
            href: `/bot-studio/${definition.slug}/edit`,
        },
    ];

    const update = useCallback(
        <K extends keyof typeof form>(
            key: K,
            value: (typeof form)[K],
        ) => {
            setForm((prev) => ({ ...prev, [key]: value }));
        },
        [],
    );

    function handleSave() {
        setSaving(true);
        router.put(
            `/bot-studio/${definition.slug}`,
            {
                ...form,
                conversation_starters: form.conversation_starters.filter(
                    (s) => s.trim().length > 0,
                ),
            },
            {
                preserveScroll: true,
                onFinish: () => setSaving(false),
            },
        );
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit ${definition.name}`} />
            <div className="flex h-[calc(100vh-4rem)] flex-col">
                {/* Top bar */}
                <div className="flex items-center justify-between border-b px-4 py-2">
                    <div className="flex items-center gap-3">
                        <div className="flex size-8 items-center justify-center rounded-lg bg-muted">
                            {definition.avatar_path ? (
                                <img
                                    src={definition.avatar_path}
                                    alt={definition.name}
                                    className="size-8 rounded-lg object-cover"
                                />
                            ) : (
                                <Bot className="size-4 text-muted-foreground" />
                            )}
                        </div>
                        <div>
                            <h1 className="font-mono text-sm font-bold tracking-tight">
                                {definition.name}
                            </h1>
                        </div>
                        <Badge
                            variant="outline"
                            className="text-[10px] font-mono uppercase tracking-wider"
                        >
                            {form.visibility === 'organization'
                                ? 'Organization'
                                : 'Private'}
                        </Badge>
                    </div>
                    <div className="flex items-center gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() =>
                                router.visit(
                                    `/bot-studio/${definition.slug}/edit`,
                                    {
                                        data: { wizard: '1' },
                                    },
                                )
                            }
                        >
                            <Sparkles className="mr-1.5 size-3.5" />
                            Re-run Wizard
                        </Button>
                        <Button
                            size="sm"
                            onClick={handleSave}
                            disabled={saving}
                        >
                            {saving ? 'Saving...' : 'Save'}
                        </Button>
                    </div>
                </div>

                {/* Split layout */}
                <div className="flex flex-1 overflow-hidden">
                    {/* Left: Tabbed editor */}
                    <div className="flex w-1/2 flex-col overflow-y-auto border-r">
                        <EditorPanel
                            form={form}
                            update={update}
                            availableTools={availableTools}
                            allowedModels={allowedModels}
                            knowledgeFiles={definition.knowledge_files ?? []}
                            definitionSlug={definition.slug}
                        />
                    </div>

                    {/* Right: Live Preview */}
                    <div className="flex w-1/2 flex-col">
                        <LivePreview
                            definition={definition}
                            starters={form.conversation_starters}
                        />
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}

/* ──────────────────── Editor Panel ──────────────────── */

function EditorPanel({
    form,
    update,
    availableTools,
    allowedModels,
    knowledgeFiles,
    definitionSlug,
}: {
    form: {
        system_prompt: string;
        model: string;
        temperature: number;
        max_tokens: number;
        enabled_tools: string[];
        conversation_starters: string[];
        visibility: string;
        is_published: boolean;
        category: string;
    };
    update: <K extends keyof typeof form>(
        key: K,
        value: (typeof form)[K],
    ) => void;
    availableTools: Tool[];
    allowedModels: string[];
    knowledgeFiles: KnowledgeFile[];
    definitionSlug: string;
}) {
    const promptRef = useRef<HTMLTextAreaElement>(null);

    function insertVariable(variable: string) {
        const el = promptRef.current;
        if (!el) return;
        const start = el.selectionStart;
        const end = el.selectionEnd;
        const text = form.system_prompt;
        const newText =
            text.substring(0, start) + variable + text.substring(end);
        update('system_prompt', newText);
        requestAnimationFrame(() => {
            el.focus();
            el.setSelectionRange(
                start + variable.length,
                start + variable.length,
            );
        });
    }

    function toggleTool(toolClass: string) {
        const current = form.enabled_tools;
        if (current.includes(toolClass)) {
            update(
                'enabled_tools',
                current.filter((t) => t !== toolClass),
            );
        } else {
            update('enabled_tools', [...current, toolClass]);
        }
    }

    function updateStarter(index: number, value: string) {
        const next = [...form.conversation_starters];
        next[index] = value;
        update('conversation_starters', next);
    }

    function addStarter() {
        update('conversation_starters', [
            ...form.conversation_starters,
            '',
        ]);
    }

    function removeStarter(index: number) {
        const next = form.conversation_starters.filter(
            (_, i) => i !== index,
        );
        update('conversation_starters', next.length === 0 ? [''] : next);
    }

    return (
        <Tabs defaultValue="prompt" className="flex flex-1 flex-col">
            <TabsList variant="line" className="shrink-0 px-4 pt-2">
                <TabsTrigger value="prompt">
                    <FileText className="mr-1.5 size-3.5" />
                    Prompt
                </TabsTrigger>
                <TabsTrigger value="tools">
                    <Wrench className="mr-1.5 size-3.5" />
                    Tools
                </TabsTrigger>
                <TabsTrigger value="knowledge">
                    <Upload className="mr-1.5 size-3.5" />
                    Knowledge
                </TabsTrigger>
                <TabsTrigger value="settings">
                    <Settings2 className="mr-1.5 size-3.5" />
                    Settings
                </TabsTrigger>
                <TabsTrigger value="starters">
                    <MessageCircle className="mr-1.5 size-3.5" />
                    Starters
                </TabsTrigger>
            </TabsList>

            {/* Prompt tab */}
            <TabsContent value="prompt" className="flex-1 overflow-y-auto p-4">
                <div className="flex flex-col gap-4">
                    {/* Variable buttons */}
                    <div className="flex flex-wrap gap-1.5">
                        {VARIABLE_BUTTONS.map((v) => (
                            <button
                                key={v.value}
                                type="button"
                                onClick={() => insertVariable(v.value)}
                                className="rounded border border-border bg-muted/50 px-2 py-1 font-mono text-[11px] text-muted-foreground transition-colors duration-100 hover:bg-muted hover:text-foreground"
                            >
                                {v.label}
                            </button>
                        ))}
                    </div>

                    <Textarea
                        ref={promptRef}
                        value={form.system_prompt}
                        onChange={(e) =>
                            update('system_prompt', e.target.value)
                        }
                        autoSize
                        minRows={10}
                        maxRows={30}
                        className="font-mono text-sm"
                        placeholder="Enter your system prompt..."
                    />

                    <div className="flex flex-col gap-4">
                        <div className="space-y-2">
                            <Label>Model</Label>
                            <Select
                                value={form.model}
                                onValueChange={(v) => update('model', v)}
                            >
                                <SelectTrigger className="w-full">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    {allowedModels.map((m) => (
                                        <SelectItem key={m} value={m}>
                                            {m}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>

                        <div className="space-y-2">
                            <div className="flex items-center justify-between">
                                <Label>Temperature</Label>
                                <span className="font-mono text-xs text-muted-foreground">
                                    {form.temperature.toFixed(1)}
                                </span>
                            </div>
                            <Slider
                                value={[form.temperature]}
                                onValueChange={([v]) =>
                                    update('temperature', v)
                                }
                                min={0}
                                max={1}
                                step={0.1}
                            />
                        </div>
                    </div>
                </div>
            </TabsContent>

            {/* Tools tab */}
            <TabsContent value="tools" className="flex-1 overflow-y-auto p-4">
                <div className="flex flex-col gap-2">
                    {availableTools.length === 0 ? (
                        <p className="text-sm text-muted-foreground">
                            No tools available.
                        </p>
                    ) : (
                        availableTools.map((tool) => {
                            const isLocked = !!tool.plan_required;
                            const isSelected = form.enabled_tools.includes(
                                tool.class,
                            );
                            return (
                                <button
                                    key={tool.class}
                                    type="button"
                                    disabled={isLocked}
                                    onClick={() => toggleTool(tool.class)}
                                    className={`flex items-center gap-3 rounded-lg border px-4 py-3 text-left text-sm transition-colors duration-100 ${
                                        isLocked
                                            ? 'cursor-not-allowed border-border bg-muted/30 opacity-60'
                                            : isSelected
                                              ? 'border-primary bg-primary/10'
                                              : 'border-border hover:border-primary/30'
                                    }`}
                                >
                                    <div
                                        className={`flex size-5 shrink-0 items-center justify-center rounded border ${
                                            isSelected
                                                ? 'border-primary bg-primary text-primary-foreground'
                                                : 'border-border'
                                        }`}
                                    >
                                        {isSelected && (
                                            <Check className="size-3" />
                                        )}
                                    </div>
                                    <div className="flex-1">
                                        <span className="font-medium">
                                            {tool.name}
                                        </span>
                                        {tool.description && (
                                            <p className="mt-0.5 text-xs text-muted-foreground">
                                                {tool.description}
                                            </p>
                                        )}
                                    </div>
                                    {isLocked && (
                                        <Lock className="size-4 text-muted-foreground" />
                                    )}
                                </button>
                            );
                        })
                    )}
                </div>
            </TabsContent>

            {/* Knowledge tab */}
            <TabsContent
                value="knowledge"
                className="flex-1 overflow-y-auto p-4"
            >
                <KnowledgeTab
                    files={knowledgeFiles}
                    definitionSlug={definitionSlug}
                />
            </TabsContent>

            {/* Settings tab */}
            <TabsContent
                value="settings"
                className="flex-1 overflow-y-auto p-4"
            >
                <div className="flex flex-col gap-4">
                    <div className="space-y-2">
                        <Label>Visibility</Label>
                        <div className="flex gap-3">
                            {(
                                ['private', 'organization'] as const
                            ).map((v) => (
                                <button
                                    key={v}
                                    type="button"
                                    onClick={() =>
                                        update('visibility', v)
                                    }
                                    className={`flex items-center gap-2 rounded-lg border px-4 py-2.5 text-sm font-medium transition-colors duration-100 ${
                                        form.visibility === v
                                            ? 'border-primary bg-primary/10 text-foreground'
                                            : 'border-border text-muted-foreground hover:border-primary/30'
                                    }`}
                                >
                                    {v === 'private' ? (
                                        <Lock className="size-4" />
                                    ) : (
                                        <Bot className="size-4" />
                                    )}
                                    {v === 'private'
                                        ? 'Private'
                                        : 'Organization'}
                                </button>
                            ))}
                        </div>
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="max_tokens">Max Tokens</Label>
                        <Input
                            id="max_tokens"
                            type="number"
                            value={form.max_tokens}
                            onChange={(e) =>
                                update(
                                    'max_tokens',
                                    parseInt(e.target.value, 10) || 4096,
                                )
                            }
                            min={256}
                            max={128000}
                        />
                        <p className="text-xs text-muted-foreground">
                            Maximum number of tokens the agent can generate
                            per response.
                        </p>
                    </div>

                    {/* Marketplace publishing */}
                    <div className="space-y-4 rounded-lg border border-border p-4">
                        <div className="flex items-center justify-between">
                            <div className="flex items-center gap-2">
                                <Globe className="size-4 text-muted-foreground" />
                                <Label htmlFor="publish_toggle">
                                    Publish to Marketplace
                                </Label>
                            </div>
                            <Switch
                                id="publish_toggle"
                                checked={form.is_published}
                                onCheckedChange={(checked) =>
                                    update('is_published', checked)
                                }
                            />
                        </div>
                        <p className="text-xs text-muted-foreground">
                            Publishing makes your agent visible to all
                            organizations on the platform.
                        </p>

                        {form.is_published && (
                            <div className="space-y-2">
                                <Label>Category</Label>
                                <Select
                                    value={form.category || ''}
                                    onValueChange={(v) =>
                                        update('category', v)
                                    }
                                >
                                    <SelectTrigger className="w-full">
                                        <SelectValue placeholder="Select a category" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {[
                                            'General',
                                            'Customer Support',
                                            'Sales',
                                            'Data Analysis',
                                            'Education',
                                            'Other',
                                        ].map((cat) => (
                                            <SelectItem
                                                key={cat}
                                                value={cat}
                                            >
                                                {cat}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                        )}
                    </div>
                </div>
            </TabsContent>

            {/* Starters tab */}
            <TabsContent
                value="starters"
                className="flex-1 overflow-y-auto p-4"
            >
                <div className="flex flex-col gap-4">
                    <p className="text-sm text-muted-foreground">
                        Conversation starters appear as clickable chips in
                        the chat interface.
                    </p>
                    <div className="flex flex-col gap-2">
                        {form.conversation_starters.map(
                            (starter, i) => (
                                <div
                                    key={i}
                                    className="flex items-center gap-2"
                                >
                                    <Input
                                        value={starter}
                                        onChange={(e) =>
                                            updateStarter(
                                                i,
                                                e.target.value,
                                            )
                                        }
                                        placeholder="e.g. How can you help me?"
                                        className="flex-1"
                                    />
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        onClick={() =>
                                            removeStarter(i)
                                        }
                                        className="shrink-0 text-muted-foreground hover:text-destructive"
                                    >
                                        <X className="size-4" />
                                    </Button>
                                </div>
                            ),
                        )}
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={addStarter}
                            className="w-fit"
                        >
                            <Plus className="mr-1.5 size-4" />
                            Add starter
                        </Button>
                    </div>
                </div>
            </TabsContent>
        </Tabs>
    );
}

/* ──────────────────── Knowledge Tab ──────────────────── */

const ACCEPTED_EXTENSIONS = '.pdf,.docx,.txt,.csv,.md';
const MAX_FILE_SIZE_MB = 10;

function formatFileSize(bytes: number | null): string {
    if (bytes === null || bytes === 0) return '0 B';
    const units = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    return `${(bytes / Math.pow(1024, i)).toFixed(i > 0 ? 1 : 0)} ${units[i]}`;
}

function StatusBadge({ status }: { status: string }) {
    const styles: Record<string, string> = {
        pending: 'border-muted-foreground/30 text-muted-foreground',
        processing: 'border-amber-500/30 text-amber-500',
        indexed: 'border-teal-500/30 text-teal-500',
        failed: 'border-destructive/30 text-destructive',
    };

    return (
        <Badge
            variant="outline"
            className={`text-[10px] font-mono uppercase tracking-wider ${styles[status] ?? ''}`}
        >
            {status === 'processing' && (
                <Loader2 className="mr-1 size-3 animate-spin" />
            )}
            {status}
        </Badge>
    );
}

function KnowledgeTab({
    files: initialFiles,
    definitionSlug,
}: {
    files: KnowledgeFile[];
    definitionSlug: string;
}) {
    const [files, setFiles] = useState<KnowledgeFile[]>(initialFiles);
    const [uploading, setUploading] = useState(false);
    const [dragOver, setDragOver] = useState(false);
    const [deleting, setDeleting] = useState<number | null>(null);
    const fileInputRef = useRef<HTMLInputElement>(null);

    const hasProcessing = files.some(
        (f) => f.status === 'pending' || f.status === 'processing',
    );

    // Poll for status updates while files are processing
    useEffect(() => {
        if (!hasProcessing) return;

        const interval = setInterval(() => {
            router.reload({
                only: ['definition'],
                onSuccess: (page) => {
                    const def = (page.props as { definition: AgentDefinition })
                        .definition;
                    if (def.knowledge_files) {
                        setFiles(def.knowledge_files);
                    }
                },
            });
        }, 3000);

        return () => clearInterval(interval);
    }, [hasProcessing]);

    // Sync with props when they change
    useEffect(() => {
        setFiles(initialFiles);
    }, [initialFiles]);

    async function uploadFile(file: File) {
        if (file.size > MAX_FILE_SIZE_MB * 1024 * 1024) {
            alert(`File exceeds maximum size of ${MAX_FILE_SIZE_MB} MB.`);
            return;
        }

        setUploading(true);

        const formData = new FormData();
        formData.append('file', file);

        try {
            const csrfMatch = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
            const csrfToken = csrfMatch
                ? decodeURIComponent(csrfMatch[1])
                : '';

            const res = await fetch(
                `/bot-studio/${definitionSlug}/knowledge`,
                {
                    method: 'POST',
                    credentials: 'include',
                    headers: {
                        Accept: 'application/json',
                        'X-XSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                },
            );

            if (!res.ok) {
                const data = await res.json().catch(() => null);
                const msg =
                    data?.errors?.file?.[0] ??
                    data?.message ??
                    'Upload failed.';
                alert(msg);
                return;
            }

            const data = (await res.json()) as {
                knowledge_file: KnowledgeFile;
            };
            setFiles((prev) => [...prev, data.knowledge_file]);
        } catch {
            alert('Upload failed. Please try again.');
        } finally {
            setUploading(false);
        }
    }

    function handleFileSelect(e: React.ChangeEvent<HTMLInputElement>) {
        const file = e.target.files?.[0];
        if (file) {
            uploadFile(file);
        }
        e.target.value = '';
    }

    function handleDrop(e: React.DragEvent) {
        e.preventDefault();
        setDragOver(false);
        const file = e.dataTransfer.files?.[0];
        if (file) {
            uploadFile(file);
        }
    }

    async function handleDelete(fileId: number) {
        if (!confirm('Delete this knowledge file? Its embeddings will also be removed.')) {
            return;
        }

        setDeleting(fileId);

        try {
            const csrfMatch = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
            const csrfToken = csrfMatch
                ? decodeURIComponent(csrfMatch[1])
                : '';

            await fetch(
                `/bot-studio/${definitionSlug}/knowledge/${fileId}`,
                {
                    method: 'DELETE',
                    credentials: 'include',
                    headers: {
                        Accept: 'application/json',
                        'X-XSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                },
            );

            setFiles((prev) => prev.filter((f) => f.id !== fileId));
        } catch {
            alert('Failed to delete file.');
        } finally {
            setDeleting(null);
        }
    }

    async function handleRetry(fileId: number) {
        try {
            const csrfMatch = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
            const csrfToken = csrfMatch
                ? decodeURIComponent(csrfMatch[1])
                : '';

            const res = await fetch(
                `/bot-studio/${definitionSlug}/knowledge/${fileId}/retry`,
                {
                    method: 'POST',
                    credentials: 'include',
                    headers: {
                        Accept: 'application/json',
                        'X-XSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                },
            );

            if (res.ok) {
                const data = (await res.json()) as {
                    knowledge_file: KnowledgeFile;
                };
                setFiles((prev) =>
                    prev.map((f) =>
                        f.id === fileId ? data.knowledge_file : f,
                    ),
                );
            }
        } catch {
            alert('Retry failed.');
        }
    }

    const totalSizeMb =
        files.reduce((sum, f) => sum + (f.file_size ?? 0), 0) / 1024 / 1024;
    const totalChunks = files.reduce((sum, f) => sum + f.chunk_count, 0);
    const maxTotalMb = 100;

    return (
        <div className="flex flex-col gap-4">
            {/* Storage usage */}
            {files.length > 0 && (
                <div className="flex items-center gap-2 text-xs text-muted-foreground">
                    <span className="font-mono">
                        {files.length} file{files.length !== 1 ? 's' : ''}
                    </span>
                    <span>&middot;</span>
                    <span className="font-mono">
                        {totalSizeMb.toFixed(1)} MB of {maxTotalMb} MB
                    </span>
                    <span>&middot;</span>
                    <span className="font-mono">
                        {totalChunks} chunk{totalChunks !== 1 ? 's' : ''}{' '}
                        indexed
                    </span>
                </div>
            )}

            {/* File list */}
            {files.length > 0 && (
                <div className="flex flex-col gap-2">
                    {files.map((file) => (
                        <div
                            key={file.id}
                            className="flex items-center justify-between rounded-lg border border-border px-4 py-2.5 text-sm"
                        >
                            <div className="flex min-w-0 flex-1 items-center gap-2">
                                <FileText className="size-4 shrink-0 text-muted-foreground" />
                                <span className="truncate">
                                    {file.filename}
                                </span>
                                <StatusBadge status={file.status} />
                            </div>
                            <div className="flex shrink-0 items-center gap-2">
                                {file.status === 'indexed' && (
                                    <span className="font-mono text-xs text-muted-foreground">
                                        {file.chunk_count} chunks
                                    </span>
                                )}
                                {file.status === 'failed' && (
                                    <>
                                        {file.error_message && (
                                            <span
                                                className="max-w-[200px] truncate text-xs text-destructive"
                                                title={file.error_message}
                                            >
                                                <AlertCircle className="mr-1 inline-block size-3" />
                                                {file.error_message}
                                            </span>
                                        )}
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            onClick={() =>
                                                handleRetry(file.id)
                                            }
                                            className="h-7 px-2 text-xs"
                                        >
                                            <RefreshCw className="mr-1 size-3" />
                                            Retry
                                        </Button>
                                    </>
                                )}
                                {file.file_size && (
                                    <span className="font-mono text-[11px] text-muted-foreground/60">
                                        {formatFileSize(file.file_size)}
                                    </span>
                                )}
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    className="size-7 text-muted-foreground hover:text-destructive"
                                    onClick={() => handleDelete(file.id)}
                                    disabled={deleting === file.id}
                                >
                                    {deleting === file.id ? (
                                        <Loader2 className="size-3.5 animate-spin" />
                                    ) : (
                                        <Trash2 className="size-3.5" />
                                    )}
                                </Button>
                            </div>
                        </div>
                    ))}
                </div>
            )}

            {/* Upload zone */}
            <div
                onDragOver={(e) => {
                    e.preventDefault();
                    setDragOver(true);
                }}
                onDragLeave={() => setDragOver(false)}
                onDrop={handleDrop}
                className={`flex flex-col items-center justify-center gap-3 rounded-lg border border-dashed px-6 py-12 text-center transition-colors duration-100 ${
                    dragOver
                        ? 'border-primary bg-primary/5'
                        : 'border-border'
                }`}
            >
                <Upload
                    className={`size-8 ${dragOver ? 'text-primary' : 'text-muted-foreground/50'}`}
                />
                <div>
                    <p className="text-sm font-medium text-muted-foreground">
                        {dragOver
                            ? 'Drop file to upload'
                            : 'Upload knowledge files'}
                    </p>
                    <p className="mt-1 text-xs text-muted-foreground/70">
                        PDF, DOCX, TXT, CSV, MD -- max {MAX_FILE_SIZE_MB} MB
                        per file
                    </p>
                </div>
                <Button
                    variant="outline"
                    size="sm"
                    onClick={() => fileInputRef.current?.click()}
                    disabled={uploading}
                >
                    {uploading ? (
                        <Loader2 className="mr-1.5 size-3.5 animate-spin" />
                    ) : (
                        <Upload className="mr-1.5 size-3.5" />
                    )}
                    {uploading ? 'Uploading...' : 'Choose File'}
                </Button>
                <input
                    ref={fileInputRef}
                    type="file"
                    accept={ACCEPTED_EXTENSIONS}
                    onChange={handleFileSelect}
                    className="hidden"
                />
            </div>
        </div>
    );
}

/* ──────────────────── Live Preview Chat ──────────────────── */

function LivePreview({
    definition,
    starters,
}: {
    definition: AgentDefinition;
    starters: string[];
}) {
    const [messages, setMessages] = useState<ChatMessage[]>([]);
    const [input, setInput] = useState('');
    const [streaming, setStreaming] = useState(false);
    const abortRef = useRef<AbortController | null>(null);
    const messagesEndRef = useRef<HTMLDivElement>(null);

    const scrollToBottom = useCallback(() => {
        messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    }, []);

    function clearChat() {
        if (abortRef.current) abortRef.current.abort();
        setMessages([]);
        setInput('');
        setStreaming(false);
    }

    async function sendMessage(content: string) {
        if (!content.trim() || streaming) return;

        const userMsg: ChatMessage = {
            id: crypto.randomUUID(),
            role: 'user',
            content: content.trim(),
        };
        const assistantId = crypto.randomUUID();
        const assistantMsg: ChatMessage = {
            id: assistantId,
            role: 'assistant',
            content: '',
        };

        setMessages((prev) => [...prev, userMsg, assistantMsg]);
        setInput('');
        setStreaming(true);

        const controller = new AbortController();
        abortRef.current = controller;

        try {
            const csrfMatch = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
            const csrfToken = csrfMatch
                ? decodeURIComponent(csrfMatch[1])
                : '';

            const res = await fetch(
                `/bot-studio/${definition.slug}/preview`,
                {
                    method: 'POST',
                    credentials: 'include',
                    signal: controller.signal,
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/x-ndjson',
                        'X-XSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        messages: [
                            ...messages.map((m) => ({
                                role: m.role,
                                content: m.content,
                            })),
                            { role: 'user', content: content.trim() },
                        ],
                    }),
                },
            );

            if (!res.ok || !res.body) {
                throw new Error(`Preview failed: ${res.status}`);
            }

            const reader = res.body.getReader();
            const decoder = new TextDecoder();
            let buffer = '';

            while (true) {
                const { done, value } = await reader.read();
                if (done) break;

                buffer += decoder.decode(value, { stream: true });
                const lines = buffer.split('\n');
                buffer = lines.pop() ?? '';

                for (const line of lines) {
                    if (!line.trim()) continue;
                    try {
                        const chunk = JSON.parse(line) as {
                            type?: string;
                            content?: string;
                        };
                        if (
                            chunk.type === 'text-delta' &&
                            chunk.content
                        ) {
                            setMessages((prev) =>
                                prev.map((m) =>
                                    m.id === assistantId
                                        ? {
                                              ...m,
                                              content:
                                                  m.content +
                                                  chunk.content,
                                          }
                                        : m,
                                ),
                            );
                            scrollToBottom();
                        }
                    } catch {
                        // skip malformed lines
                    }
                }
            }
        } catch (err) {
            if (
                err instanceof DOMException &&
                err.name === 'AbortError'
            ) {
                return;
            }
            setMessages((prev) =>
                prev.map((m) =>
                    m.id === assistantId
                        ? {
                              ...m,
                              content:
                                  m.content ||
                                  'Preview failed. Please try again.',
                          }
                        : m,
                ),
            );
        } finally {
            setStreaming(false);
            abortRef.current = null;
            scrollToBottom();
        }
    }

    const activeStarters = starters.filter((s) => s.trim().length > 0);

    return (
        <div className="flex flex-1 flex-col">
            {/* Preview header */}
            <div className="flex items-center justify-between border-b px-4 py-2">
                <div className="flex items-center gap-2">
                    <span className="inline-block size-2 rounded-full bg-green-500" />
                    <span className="font-mono text-xs font-medium uppercase tracking-wider text-muted-foreground">
                        Live Preview
                    </span>
                </div>
                <Button
                    variant="ghost"
                    size="sm"
                    onClick={clearChat}
                    className="text-xs text-muted-foreground"
                >
                    <Trash2 className="mr-1 size-3" />
                    Clear chat
                </Button>
            </div>

            {/* Messages area */}
            <div className="flex-1 overflow-y-auto p-4">
                {messages.length === 0 ? (
                    <div className="flex h-full flex-col items-center justify-center gap-4">
                        <div className="flex size-12 items-center justify-center rounded-xl bg-muted">
                            {definition.avatar_path ? (
                                <img
                                    src={definition.avatar_path}
                                    alt={definition.name}
                                    className="size-12 rounded-xl object-cover"
                                />
                            ) : (
                                <Bot className="size-6 text-muted-foreground" />
                            )}
                        </div>
                        <p className="font-mono text-sm font-semibold">
                            {definition.name}
                        </p>
                        {activeStarters.length > 0 && (
                            <div className="flex flex-wrap justify-center gap-2">
                                {activeStarters.map((starter, i) => (
                                    <button
                                        key={i}
                                        type="button"
                                        onClick={() =>
                                            sendMessage(starter)
                                        }
                                        className="rounded-full border border-border px-3 py-1.5 text-xs text-muted-foreground transition-colors duration-100 hover:border-primary/30 hover:text-foreground"
                                    >
                                        {starter}
                                    </button>
                                ))}
                            </div>
                        )}
                    </div>
                ) : (
                    <div className="flex flex-col gap-4">
                        {messages.map((msg) => (
                            <div
                                key={msg.id}
                                className={`flex ${msg.role === 'user' ? 'justify-end' : 'justify-start'}`}
                            >
                                <div
                                    className={`max-w-[80%] rounded-lg px-3 py-2 text-sm ${
                                        msg.role === 'user'
                                            ? 'bg-primary text-primary-foreground'
                                            : 'bg-muted'
                                    }`}
                                >
                                    <p className="whitespace-pre-wrap">
                                        {msg.content}
                                        {msg.role === 'assistant' &&
                                            streaming &&
                                            msg ===
                                                messages[
                                                    messages.length - 1
                                                ] && (
                                                <span className="ml-0.5 inline-block size-1.5 animate-pulse rounded-full bg-current" />
                                            )}
                                    </p>
                                </div>
                            </div>
                        ))}
                        <div ref={messagesEndRef} />
                    </div>
                )}
            </div>

            {/* Ephemeral note */}
            <div className="px-4">
                <p className="text-center text-[11px] text-muted-foreground/60">
                    Preview messages are ephemeral -- not saved or billed
                </p>
            </div>

            {/* Chat input */}
            <div className="border-t p-4">
                <form
                    onSubmit={(e) => {
                        e.preventDefault();
                        sendMessage(input);
                    }}
                    className="flex items-end gap-2"
                >
                    <Textarea
                        value={input}
                        onChange={(e) => setInput(e.target.value)}
                        onKeyDown={(e) => {
                            if (
                                e.key === 'Enter' &&
                                !e.shiftKey
                            ) {
                                e.preventDefault();
                                sendMessage(input);
                            }
                        }}
                        placeholder="Type a message..."
                        autoSize
                        minRows={1}
                        maxRows={4}
                        className="flex-1 text-sm"
                    />
                    <Button
                        type="submit"
                        size="icon"
                        disabled={
                            !input.trim() || streaming
                        }
                    >
                        <Send className="size-4" />
                    </Button>
                </form>
            </div>
        </div>
    );
}
