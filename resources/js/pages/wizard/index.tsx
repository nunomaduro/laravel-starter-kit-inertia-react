import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import type { BreadcrumbItem, SharedData } from '@/types';
import { Head, usePage } from '@inertiajs/react';
import { Bot, Check, ChevronLeft, ChevronRight, Loader2, Package, Sparkles, Wand2 } from 'lucide-react';
import { useCallback, useState } from 'react';

type Domain = {
    slug: string;
    name: string;
    description: string;
    features: string[];
};

type PreviewData = {
    modules: Array<{
        slug: string;
        name: string;
        description: string;
        models: number;
        features: string[];
    }>;
    summary: {
        total_modules: number;
        total_models: number;
        total_pages: number;
        includes: string[];
    };
};

type Props = {
    availableDomains: Domain[];
};

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Project Wizard', href: '/wizard' }];

const STEPS = ['Describe', 'Modules', 'Preview', 'Generate'] as const;

export default function WizardIndex() {
    const { availableDomains } = usePage<Props & SharedData>().props;
    const [step, setStep] = useState(0);
    const [projectName, setProjectName] = useState('');
    const [description, setDescription] = useState('');
    const [selectedModules, setSelectedModules] = useState<string[]>([]);
    const [aiRecommendation, setAiRecommendation] = useState<{ slugs: string[]; reasoning: string } | null>(null);
    const [preview, setPreview] = useState<PreviewData | null>(null);
    const [analyzing, setAnalyzing] = useState(false);
    const [generating, setGenerating] = useState(false);
    const [result, setResult] = useState<{ success: boolean; message: string; next_steps: string[] } | null>(null);

    const analyzeDescription = useCallback(async () => {
        if (!description.trim()) return;
        setAnalyzing(true);
        try {
            const res = await fetch('/wizard/analyze', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '' },
                body: JSON.stringify({ description }),
            });
            const data = await res.json();
            setAiRecommendation(data);
            if (data.slugs?.length > 0) {
                setSelectedModules(data.slugs);
            }
        } catch {
            setAiRecommendation({ slugs: [], reasoning: 'Could not reach AI. Select modules manually.' });
        }
        setAnalyzing(false);
        setStep(1);
    }, [description]);

    const loadPreview = useCallback(async () => {
        if (selectedModules.length === 0) return;
        try {
            const res = await fetch('/wizard/preview', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '' },
                body: JSON.stringify({ modules: selectedModules }),
            });
            setPreview(await res.json());
        } catch {
            // Silent fail
        }
        setStep(2);
    }, [selectedModules]);

    const generate = useCallback(async () => {
        setGenerating(true);
        try {
            const res = await fetch('/wizard/generate', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '' },
                body: JSON.stringify({ name: projectName, description, modules: selectedModules }),
            });
            setResult(await res.json());
            setStep(3);
        } catch {
            setResult({ success: false, message: 'Generation failed. Try again.', next_steps: [] });
            setStep(3);
        }
        setGenerating(false);
    }, [projectName, description, selectedModules]);

    const toggleModule = (slug: string) => {
        setSelectedModules((prev) => (prev.includes(slug) ? prev.filter((s) => s !== slug) : [...prev, slug]));
    };

    return (
        <AppSidebarLayout breadcrumbs={breadcrumbs}>
            <Head title="Project Wizard" />

            <div className="mx-auto max-w-3xl px-6 py-8">
                {/* Step indicator */}
                <div className="mb-8 flex items-center justify-center gap-2">
                    {STEPS.map((label, i) => (
                        <div key={label} className="flex items-center gap-2">
                            <div
                                className={`flex h-8 w-8 items-center justify-center rounded-full text-sm font-medium transition-colors ${
                                    i === step
                                        ? 'bg-primary text-primary-foreground'
                                        : i < step
                                          ? 'bg-primary/20 text-primary'
                                          : 'bg-muted text-muted-foreground'
                                }`}
                            >
                                {i < step ? <Check className="h-4 w-4" /> : i + 1}
                            </div>
                            <span className={`text-sm ${i === step ? 'font-medium' : 'text-muted-foreground'}`}>{label}</span>
                            {i < STEPS.length - 1 && <div className="mx-2 h-px w-8 bg-border" />}
                        </div>
                    ))}
                </div>

                {/* Step 0: Describe */}
                {step === 0 && (
                    <div className="space-y-6">
                        <div className="text-center">
                            <Wand2 className="mx-auto mb-4 h-12 w-12 text-primary" />
                            <h1 className="text-2xl font-bold">Describe Your Project</h1>
                            <p className="mt-2 text-muted-foreground">Tell us what you want to build and AI will recommend the right modules.</p>
                        </div>

                        <div>
                            <Label htmlFor="name">Project Name</Label>
                            <Input id="name" value={projectName} onChange={(e) => setProjectName(e.target.value)} placeholder="e.g., AcmeHR, FleetTracker" className="mt-1" />
                        </div>

                        <div>
                            <Label htmlFor="description">What are you building?</Label>
                            <textarea
                                id="description"
                                value={description}
                                onChange={(e) => setDescription(e.target.value)}
                                placeholder="e.g., An HR management system for a 200-person logistics company with leave management, employee profiles, performance reviews, and an AI assistant that can answer HR policy questions"
                                className="mt-1 w-full rounded-md border bg-background px-3 py-2 text-sm min-h-[120px] focus:outline-none focus:ring-2 focus:ring-ring"
                            />
                        </div>

                        <Button onClick={analyzeDescription} disabled={!description.trim() || !projectName.trim() || analyzing} className="w-full">
                            {analyzing ? (
                                <>
                                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                    AI analyzing...
                                </>
                            ) : (
                                <>
                                    <Sparkles className="mr-2 h-4 w-4" />
                                    Analyze & Recommend Modules
                                </>
                            )}
                        </Button>
                    </div>
                )}

                {/* Step 1: Select Modules */}
                {step === 1 && (
                    <div className="space-y-6">
                        <div className="text-center">
                            <Package className="mx-auto mb-4 h-12 w-12 text-primary" />
                            <h1 className="text-2xl font-bold">Select Modules</h1>
                            {aiRecommendation && (
                                <p className="mt-2 text-sm text-muted-foreground">
                                    <Bot className="mr-1 inline h-4 w-4" />
                                    {aiRecommendation.reasoning}
                                </p>
                            )}
                        </div>

                        <div className="grid gap-3">
                            {availableDomains.map((domain) => {
                                const isSelected = selectedModules.includes(domain.slug);
                                const isRecommended = aiRecommendation?.slugs.includes(domain.slug);

                                return (
                                    <button
                                        key={domain.slug}
                                        onClick={() => toggleModule(domain.slug)}
                                        className={`rounded-lg border p-4 text-left transition-all ${
                                            isSelected ? 'border-primary bg-primary/5 ring-1 ring-primary' : 'hover:border-foreground/25'
                                        }`}
                                    >
                                        <div className="flex items-start justify-between">
                                            <div>
                                                <div className="flex items-center gap-2">
                                                    <h3 className="font-semibold">{domain.name}</h3>
                                                    {isRecommended && (
                                                        <span className="rounded-full bg-primary/10 px-2 py-0.5 text-xs font-medium text-primary">
                                                            AI Recommended
                                                        </span>
                                                    )}
                                                </div>
                                                <p className="mt-1 text-sm text-muted-foreground">{domain.description}</p>
                                                <div className="mt-2 flex flex-wrap gap-1">
                                                    {domain.features.slice(0, 3).map((f) => (
                                                        <span key={f} className="rounded-md bg-muted px-2 py-0.5 text-xs">
                                                            {f}
                                                        </span>
                                                    ))}
                                                </div>
                                            </div>
                                            <div
                                                className={`flex h-6 w-6 items-center justify-center rounded-full border-2 ${
                                                    isSelected ? 'border-primary bg-primary text-primary-foreground' : 'border-muted-foreground/30'
                                                }`}
                                            >
                                                {isSelected && <Check className="h-3.5 w-3.5" />}
                                            </div>
                                        </div>
                                    </button>
                                );
                            })}
                        </div>

                        <div className="flex gap-3">
                            <Button variant="outline" onClick={() => setStep(0)}>
                                <ChevronLeft className="mr-1 h-4 w-4" />
                                Back
                            </Button>
                            <Button onClick={loadPreview} disabled={selectedModules.length === 0} className="flex-1">
                                Preview Configuration
                                <ChevronRight className="ml-1 h-4 w-4" />
                            </Button>
                        </div>
                    </div>
                )}

                {/* Step 2: Preview */}
                {step === 2 && preview && (
                    <div className="space-y-6">
                        <div className="text-center">
                            <h1 className="text-2xl font-bold">Preview: {projectName}</h1>
                            <p className="mt-2 text-muted-foreground">Here's what will be configured for your project.</p>
                        </div>

                        {/* Selected modules */}
                        <div className="rounded-lg border p-4">
                            <h3 className="font-semibold mb-3">Selected Modules</h3>
                            {preview.modules.map((mod) => (
                                <div key={mod.slug} className="flex items-center justify-between py-2 border-b last:border-0">
                                    <div>
                                        <span className="font-medium">{mod.name}</span>
                                        <span className="ml-2 text-sm text-muted-foreground">({mod.models} models)</span>
                                    </div>
                                    <Check className="h-4 w-4 text-green-500" />
                                </div>
                            ))}
                        </div>

                        {/* Summary */}
                        <div className="rounded-lg border p-4">
                            <h3 className="font-semibold mb-3">What You'll Get</h3>
                            <div className="grid grid-cols-3 gap-4 text-center mb-4">
                                <div>
                                    <div className="text-2xl font-bold">{preview.summary.total_modules}</div>
                                    <div className="text-xs text-muted-foreground">Modules</div>
                                </div>
                                <div>
                                    <div className="text-2xl font-bold">{preview.summary.total_models}</div>
                                    <div className="text-xs text-muted-foreground">Models</div>
                                </div>
                                <div>
                                    <div className="text-2xl font-bold">{preview.summary.total_pages}</div>
                                    <div className="text-xs text-muted-foreground">Pages</div>
                                </div>
                            </div>
                            <h4 className="text-sm font-medium mb-2">Always Included:</h4>
                            <ul className="space-y-1">
                                {preview.summary.includes.map((item) => (
                                    <li key={item} className="flex items-center gap-2 text-sm text-muted-foreground">
                                        <Check className="h-3.5 w-3.5 text-green-500" />
                                        {item}
                                    </li>
                                ))}
                            </ul>
                        </div>

                        <div className="flex gap-3">
                            <Button variant="outline" onClick={() => setStep(1)}>
                                <ChevronLeft className="mr-1 h-4 w-4" />
                                Back
                            </Button>
                            <Button onClick={generate} disabled={generating} className="flex-1">
                                {generating ? (
                                    <>
                                        <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                        Generating...
                                    </>
                                ) : (
                                    <>
                                        <Wand2 className="mr-2 h-4 w-4" />
                                        Generate {projectName}
                                    </>
                                )}
                            </Button>
                        </div>
                    </div>
                )}

                {/* Step 3: Result */}
                {step === 3 && result && (
                    <div className="space-y-6 text-center">
                        <div>
                            {result.success ? (
                                <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-green-100">
                                    <Check className="h-8 w-8 text-green-600" />
                                </div>
                            ) : (
                                <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-red-100">
                                    <X className="h-8 w-8 text-red-600" />
                                </div>
                            )}
                            <h1 className="text-2xl font-bold">{result.message}</h1>
                        </div>

                        {result.next_steps.length > 0 && (
                            <div className="mx-auto max-w-md rounded-lg border p-4 text-left">
                                <h3 className="font-semibold mb-3">Next Steps</h3>
                                <ol className="space-y-2">
                                    {result.next_steps.map((step, i) => (
                                        <li key={i} className="flex gap-3 text-sm">
                                            <span className="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary/10 text-xs font-medium text-primary">
                                                {i + 1}
                                            </span>
                                            <code className="rounded bg-muted px-2 py-0.5 font-mono text-xs">{step}</code>
                                        </li>
                                    ))}
                                </ol>
                            </div>
                        )}

                        <div className="flex justify-center gap-3">
                            <a href="/showcase">
                                <Button variant="outline">Explore Features</Button>
                            </a>
                            <a href="/dashboard">
                                <Button>Go to Dashboard</Button>
                            </a>
                        </div>
                    </div>
                )}
            </div>
        </AppSidebarLayout>
    );
}

function X({ className }: { className?: string }) {
    return (
        <svg className={className} fill="none" viewBox="0 0 24 24" strokeWidth={2} stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
    );
}
