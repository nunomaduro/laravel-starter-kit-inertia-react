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
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import {
    ArrowLeft,
    ArrowRight,
    Check,
    Lock,
    MessageCircle,
    Plus,
    Settings2,
    Sparkles,
    User,
    Wrench,
    X,
} from 'lucide-react';
import { useCallback, useMemo, useState } from 'react';

interface Tool {
    class: string;
    name: string;
    description?: string;
    plan_required?: string | null;
}

interface Props {
    availableTools: Tool[];
    allowedModels: string[];
}

interface WizardState {
    // Step 1: Identity
    name: string;
    description: string;
    visibility: 'private' | 'organization';
    // Step 2: Persona
    role: string;
    tone: string;
    topics: string;
    refusals: string;
    systemPrompt: string;
    manualEdit: boolean;
    // Step 3: Tools
    enabledTools: string[];
    // Step 4: Starters
    conversationStarters: string[];
    // Step 5: Review
    model: string;
    temperature: number;
}

const TONES = ['Professional', 'Friendly', 'Casual', 'Technical', 'Empathetic'];

const STEPS = [
    { id: 1, name: 'Identity', icon: User },
    { id: 2, name: 'Persona', icon: Sparkles },
    { id: 3, name: 'Tools', icon: Wrench },
    { id: 4, name: 'Starters', icon: MessageCircle },
    { id: 5, name: 'Review', icon: Settings2 },
];

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Bot Studio', href: '/bot-studio' },
    { title: 'Create Agent', href: '/bot-studio/create' },
];

function generateSlug(name: string): string {
    return name
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-|-$/g, '');
}

function generateSystemPrompt(state: WizardState): string {
    const parts: string[] = [];
    if (state.role) {
        parts.push(`You are ${state.role}.`);
    }
    if (state.tone) {
        parts.push(`Communicate in a ${state.tone.toLowerCase()} tone.`);
    }
    if (state.topics) {
        parts.push(`You are knowledgeable about: ${state.topics}.`);
    }
    if (state.refusals) {
        parts.push(`You must refuse to: ${state.refusals}.`);
    }
    return parts.join('\n\n');
}

export default function BotStudioCreate({
    availableTools,
    allowedModels,
}: Props) {
    const [step, setStep] = useState(1);
    const [submitting, setSubmitting] = useState(false);
    const [state, setState] = useState<WizardState>({
        name: '',
        description: '',
        visibility: 'private',
        role: '',
        tone: '',
        topics: '',
        refusals: '',
        systemPrompt: '',
        manualEdit: false,
        enabledTools: [],
        conversationStarters: [''],
        model: allowedModels[0] ?? 'gpt-4o-mini',
        temperature: 0.7,
    });

    const update = useCallback(
        <K extends keyof WizardState>(key: K, value: WizardState[K]) => {
            setState((prev) => ({ ...prev, [key]: value }));
        },
        [],
    );

    const generatedPrompt = useMemo(() => generateSystemPrompt(state), [state]);
    const effectivePrompt = state.manualEdit
        ? state.systemPrompt
        : generatedPrompt;

    const canProceed = useMemo(() => {
        switch (step) {
            case 1:
                return state.name.trim().length > 0;
            case 2:
                return state.role.trim().length > 0 || effectivePrompt.length > 0;
            default:
                return true;
        }
    }, [step, state.name, state.role, effectivePrompt]);

    function handleSubmit() {
        setSubmitting(true);
        router.post(
            '/bot-studio',
            {
                name: state.name,
                description: state.description,
                visibility: state.visibility,
                system_prompt: effectivePrompt,
                model: state.model,
                temperature: state.temperature,
                enabled_tools: state.enabledTools,
                conversation_starters: state.conversationStarters.filter(
                    (s) => s.trim().length > 0,
                ),
                wizard_answers: {
                    role: state.role,
                    tone: state.tone,
                    topics: state.topics,
                    refusals: state.refusals,
                },
            },
            {
                onFinish: () => setSubmitting(false),
            },
        );
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Agent" />
            <div className="mx-auto flex w-full max-w-3xl flex-col gap-8 p-4 py-8">
                {/* Progress bar */}
                <div className="flex items-center gap-1">
                    {STEPS.map((s, i) => {
                        const Icon = s.icon;
                        const isActive = step === s.id;
                        const isComplete = step > s.id;
                        return (
                            <div key={s.id} className="flex flex-1 items-center gap-1">
                                <button
                                    type="button"
                                    onClick={() => {
                                        if (isComplete) setStep(s.id);
                                    }}
                                    disabled={!isComplete && !isActive}
                                    className={`flex items-center gap-2 rounded-md px-2 py-1.5 text-xs font-medium transition-colors duration-100 ${
                                        isActive
                                            ? 'text-foreground'
                                            : isComplete
                                              ? 'cursor-pointer text-muted-foreground hover:text-foreground'
                                              : 'cursor-default text-muted-foreground/50'
                                    }`}
                                >
                                    <span
                                        className={`flex size-6 items-center justify-center rounded-full text-[10px] font-mono font-bold ${
                                            isComplete
                                                ? 'bg-primary text-primary-foreground'
                                                : isActive
                                                  ? 'border-2 border-primary text-primary'
                                                  : 'border border-border text-muted-foreground/50'
                                        }`}
                                    >
                                        {isComplete ? (
                                            <Check className="size-3" />
                                        ) : (
                                            s.id
                                        )}
                                    </span>
                                    <span className="hidden sm:inline">
                                        <Icon className="mr-1 inline size-3.5" />
                                        {s.name}
                                    </span>
                                </button>
                                {i < STEPS.length - 1 && (
                                    <div
                                        className={`h-px flex-1 ${
                                            isComplete
                                                ? 'bg-primary'
                                                : 'bg-border'
                                        }`}
                                    />
                                )}
                            </div>
                        );
                    })}
                </div>

                {/* Step content */}
                <div className="min-h-[400px]">
                    {step === 1 && (
                        <StepIdentity
                            state={state}
                            update={update}
                        />
                    )}
                    {step === 2 && (
                        <StepPersona
                            state={state}
                            update={update}
                            effectivePrompt={effectivePrompt}
                        />
                    )}
                    {step === 3 && (
                        <StepTools
                            state={state}
                            update={update}
                            availableTools={availableTools}
                        />
                    )}
                    {step === 4 && (
                        <StepStarters state={state} update={update} />
                    )}
                    {step === 5 && (
                        <StepReview
                            state={state}
                            update={update}
                            effectivePrompt={effectivePrompt}
                            allowedModels={allowedModels}
                            availableTools={availableTools}
                        />
                    )}
                </div>

                {/* Navigation */}
                <div className="flex items-center justify-between border-t pt-4">
                    <Button
                        variant="ghost"
                        size="sm"
                        onClick={() => setStep((s) => s - 1)}
                        disabled={step === 1}
                    >
                        <ArrowLeft className="mr-1.5 size-4" />
                        Back
                    </Button>
                    {step < 5 ? (
                        <Button
                            size="sm"
                            onClick={() => setStep((s) => s + 1)}
                            disabled={!canProceed}
                        >
                            Next: {STEPS[step]?.name}
                            <ArrowRight className="ml-1.5 size-4" />
                        </Button>
                    ) : (
                        <Button
                            size="sm"
                            onClick={handleSubmit}
                            disabled={submitting || !canProceed}
                        >
                            {submitting ? 'Creating...' : 'Create Agent'}
                        </Button>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}

/* ──────────────────── Step Components ──────────────────── */

function StepIdentity({
    state,
    update,
}: {
    state: WizardState;
    update: <K extends keyof WizardState>(key: K, value: WizardState[K]) => void;
}) {
    const slug = generateSlug(state.name);
    return (
        <div className="flex flex-col gap-6">
            <div>
                <h2 className="font-mono text-xl font-bold tracking-tight">
                    Identity
                </h2>
                <p className="mt-1 font-sans text-sm text-muted-foreground">
                    Give your agent a name and description.
                </p>
            </div>

            <div className="flex flex-col gap-4">
                <div className="space-y-2">
                    <Label htmlFor="name">Name</Label>
                    <Input
                        id="name"
                        value={state.name}
                        onChange={(e) => update('name', e.target.value)}
                        placeholder="e.g. Customer Support Agent"
                    />
                    {slug && (
                        <p className="font-mono text-xs text-muted-foreground">
                            Slug: {slug}
                        </p>
                    )}
                </div>

                <div className="space-y-2">
                    <Label htmlFor="description">Description</Label>
                    <Textarea
                        id="description"
                        value={state.description}
                        onChange={(e) => update('description', e.target.value)}
                        placeholder="Briefly describe what this agent does..."
                        autoSize
                        minRows={3}
                        maxRows={6}
                    />
                </div>

                <div className="space-y-2">
                    <Label>Visibility</Label>
                    <div className="flex gap-3">
                        {(['private', 'organization'] as const).map((v) => (
                            <button
                                key={v}
                                type="button"
                                onClick={() => update('visibility', v)}
                                className={`flex items-center gap-2 rounded-lg border px-4 py-2.5 text-sm font-medium transition-colors duration-100 ${
                                    state.visibility === v
                                        ? 'border-primary bg-primary/10 text-foreground'
                                        : 'border-border text-muted-foreground hover:border-primary/30'
                                }`}
                            >
                                {v === 'private' ? (
                                    <Lock className="size-4" />
                                ) : (
                                    <User className="size-4" />
                                )}
                                {v === 'private' ? 'Private' : 'Organization'}
                            </button>
                        ))}
                    </div>
                </div>
            </div>
        </div>
    );
}

function StepPersona({
    state,
    update,
    effectivePrompt,
}: {
    state: WizardState;
    update: <K extends keyof WizardState>(key: K, value: WizardState[K]) => void;
    effectivePrompt: string;
}) {
    return (
        <div className="flex flex-col gap-6">
            <div>
                <h2 className="font-mono text-xl font-bold tracking-tight">
                    Persona
                </h2>
                <p className="mt-1 font-sans text-sm text-muted-foreground">
                    Define how your agent should behave.
                </p>
            </div>

            <div className="flex flex-col gap-4">
                <div className="space-y-2">
                    <Label htmlFor="role">
                        What role should this agent play?
                    </Label>
                    <Input
                        id="role"
                        value={state.role}
                        onChange={(e) => update('role', e.target.value)}
                        placeholder="e.g. a helpful customer support specialist"
                    />
                </div>

                <div className="space-y-2">
                    <Label>What tone?</Label>
                    <div className="flex flex-wrap gap-2">
                        {TONES.map((tone) => (
                            <button
                                key={tone}
                                type="button"
                                onClick={() =>
                                    update(
                                        'tone',
                                        state.tone === tone ? '' : tone,
                                    )
                                }
                                className={`rounded-md border px-3 py-1.5 text-sm font-medium transition-colors duration-100 ${
                                    state.tone === tone
                                        ? 'border-primary bg-primary/10 text-foreground'
                                        : 'border-border text-muted-foreground hover:border-primary/30'
                                }`}
                            >
                                {tone}
                            </button>
                        ))}
                    </div>
                </div>

                <div className="space-y-2">
                    <Label htmlFor="topics">
                        What topics should it know about?
                    </Label>
                    <Input
                        id="topics"
                        value={state.topics}
                        onChange={(e) => update('topics', e.target.value)}
                        placeholder="e.g. product features, pricing, troubleshooting"
                    />
                </div>

                <div className="space-y-2">
                    <Label htmlFor="refusals">
                        What should it refuse to do?
                    </Label>
                    <Textarea
                        id="refusals"
                        value={state.refusals}
                        onChange={(e) => update('refusals', e.target.value)}
                        placeholder="e.g. provide medical advice, share internal company data"
                        autoSize
                        minRows={2}
                        maxRows={4}
                    />
                </div>

                {/* Generated prompt preview */}
                <div className="space-y-2 rounded-lg border border-border bg-muted/30 p-4">
                    <div className="flex items-center justify-between">
                        <Label className="text-xs uppercase tracking-wider text-muted-foreground">
                            System Prompt Preview
                        </Label>
                        <button
                            type="button"
                            onClick={() => {
                                if (!state.manualEdit) {
                                    update('systemPrompt', effectivePrompt);
                                }
                                update('manualEdit', !state.manualEdit);
                            }}
                            className="text-xs text-primary hover:underline"
                        >
                            {state.manualEdit
                                ? 'Use generated'
                                : 'Edit manually'}
                        </button>
                    </div>
                    {state.manualEdit ? (
                        <Textarea
                            value={state.systemPrompt}
                            onChange={(e) =>
                                update('systemPrompt', e.target.value)
                            }
                            autoSize
                            minRows={4}
                            maxRows={12}
                            className="bg-background font-mono text-xs"
                        />
                    ) : (
                        <pre className="max-h-48 overflow-y-auto whitespace-pre-wrap font-mono text-xs text-muted-foreground">
                            {effectivePrompt || 'Fill in the fields above to generate a prompt...'}
                        </pre>
                    )}
                </div>
            </div>
        </div>
    );
}

function StepTools({
    state,
    update,
    availableTools,
}: {
    state: WizardState;
    update: <K extends keyof WizardState>(key: K, value: WizardState[K]) => void;
    availableTools: Tool[];
}) {
    function toggleTool(toolClass: string) {
        const current = state.enabledTools;
        if (current.includes(toolClass)) {
            update(
                'enabledTools',
                current.filter((t) => t !== toolClass),
            );
        } else {
            update('enabledTools', [...current, toolClass]);
        }
    }

    return (
        <div className="flex flex-col gap-6">
            <div>
                <h2 className="font-mono text-xl font-bold tracking-tight">
                    Tools
                </h2>
                <p className="mt-1 font-sans text-sm text-muted-foreground">
                    Select which tools your agent can use.
                </p>
            </div>

            {availableTools.length === 0 ? (
                <p className="text-sm text-muted-foreground">
                    No tools available. Tools can be registered by modules.
                </p>
            ) : (
                <div className="flex flex-col gap-2">
                    {availableTools.map((tool) => {
                        const isLocked = !!tool.plan_required;
                        const isSelected = state.enabledTools.includes(
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
                    })}
                </div>
            )}
        </div>
    );
}

function StepStarters({
    state,
    update,
}: {
    state: WizardState;
    update: <K extends keyof WizardState>(key: K, value: WizardState[K]) => void;
}) {
    function updateStarter(index: number, value: string) {
        const next = [...state.conversationStarters];
        next[index] = value;
        update('conversationStarters', next);
    }

    function addStarter() {
        update('conversationStarters', [...state.conversationStarters, '']);
    }

    function removeStarter(index: number) {
        const next = state.conversationStarters.filter((_, i) => i !== index);
        update(
            'conversationStarters',
            next.length === 0 ? [''] : next,
        );
    }

    return (
        <div className="flex flex-col gap-6">
            <div>
                <h2 className="font-mono text-xl font-bold tracking-tight">
                    Conversation Starters
                </h2>
                <p className="mt-1 font-sans text-sm text-muted-foreground">
                    Suggest opening messages users can click to start a
                    conversation.
                </p>
            </div>

            <div className="flex flex-col gap-2">
                {state.conversationStarters.map((starter, i) => (
                    <div key={i} className="flex items-center gap-2">
                        <Input
                            value={starter}
                            onChange={(e) => updateStarter(i, e.target.value)}
                            placeholder={`e.g. How can you help me?`}
                            className="flex-1"
                        />
                        <Button
                            variant="ghost"
                            size="icon"
                            onClick={() => removeStarter(i)}
                            className="shrink-0 text-muted-foreground hover:text-destructive"
                        >
                            <X className="size-4" />
                        </Button>
                    </div>
                ))}
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
    );
}

function StepReview({
    state,
    update,
    effectivePrompt,
    allowedModels,
    availableTools,
}: {
    state: WizardState;
    update: <K extends keyof WizardState>(key: K, value: WizardState[K]) => void;
    effectivePrompt: string;
    allowedModels: string[];
    availableTools: Tool[];
}) {
    const enabledToolNames = availableTools
        .filter((t) => state.enabledTools.includes(t.class))
        .map((t) => t.name);

    return (
        <div className="flex flex-col gap-6">
            <div>
                <h2 className="font-mono text-xl font-bold tracking-tight">
                    Review
                </h2>
                <p className="mt-1 font-sans text-sm text-muted-foreground">
                    Review your agent configuration before creating.
                </p>
            </div>

            {/* Summary */}
            <div className="flex flex-col gap-4 rounded-lg border border-border p-4">
                <ReviewRow label="Name" value={state.name} />
                <ReviewRow
                    label="Visibility"
                    value={
                        state.visibility === 'organization'
                            ? 'Organization'
                            : 'Private'
                    }
                />
                {state.description && (
                    <ReviewRow
                        label="Description"
                        value={state.description}
                    />
                )}
                <ReviewRow
                    label="Tools"
                    value={
                        enabledToolNames.length > 0
                            ? enabledToolNames.join(', ')
                            : 'None'
                    }
                />
                <ReviewRow
                    label="Starters"
                    value={
                        state.conversationStarters.filter(
                            (s) => s.trim().length > 0,
                        ).length + ' starter(s)'
                    }
                />
                <div className="space-y-1">
                    <span className="font-mono text-[11px] font-medium uppercase tracking-wider text-muted-foreground">
                        System Prompt
                    </span>
                    <pre className="max-h-32 overflow-y-auto whitespace-pre-wrap rounded-md bg-muted/50 p-3 font-mono text-xs">
                        {effectivePrompt}
                    </pre>
                </div>
            </div>

            {/* Model + Temperature */}
            <div className="flex flex-col gap-4">
                <div className="space-y-2">
                    <Label>Model</Label>
                    <Select
                        value={state.model}
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
                            {state.temperature.toFixed(1)}
                        </span>
                    </div>
                    <Slider
                        value={[state.temperature]}
                        onValueChange={([v]) => update('temperature', v)}
                        min={0}
                        max={1}
                        step={0.1}
                    />
                    <div className="flex justify-between text-[11px] text-muted-foreground">
                        <span>Precise</span>
                        <span>Creative</span>
                    </div>
                </div>
            </div>
        </div>
    );
}

function ReviewRow({ label, value }: { label: string; value: string }) {
    return (
        <div className="flex items-baseline justify-between gap-4">
            <span className="font-mono text-[11px] font-medium uppercase tracking-wider text-muted-foreground">
                {label}
            </span>
            <span className="text-right text-sm">{value}</span>
        </div>
    );
}
