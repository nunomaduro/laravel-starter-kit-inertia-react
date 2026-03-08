import { Loader2Icon, MicIcon, MicOffIcon } from 'lucide-react';
import * as React from 'react';

import { Button } from '@/components/ui/button';
import { useReducedMotion } from '@/hooks/use-reduced-motion';
import { cn } from '@/lib/utils';

export type VoiceInputState = 'idle' | 'listening' | 'processing' | 'error';

export interface VoiceInputProps {
    /** Called when the user finishes speaking and a transcript is ready. */
    onTranscript?: (text: string) => void;
    /** Called when recording starts. */
    onStart?: () => void;
    /** Called when recording stops (before transcript is ready). */
    onStop?: () => void;
    /** Called on browser-level errors. */
    onError?: (error: string) => void;
    /** Disable the button. */
    disabled?: boolean;
    /** Language BCP-47 code for recognition (default: browser default). */
    lang?: string;
    /** Button size variant. */
    size?: 'sm' | 'default' | 'lg';
    className?: string;
}

interface SpeechRecognitionLike {
    continuous: boolean;
    interimResults: boolean;
    lang: string;
    start(): void;
    stop(): void;
    onstart: (() => void) | null;
    onresult: ((event: { results: { transcript: string }[][] }) => void) | null;
    onerror: ((event: { error: string }) => void) | null;
    onend: (() => void) | null;
}

/**
 * Voice input button using the Web Speech API (SpeechRecognition).
 * Falls back gracefully when the API is unavailable.
 * Respects `prefers-reduced-motion` for the pulsing animation.
 */
export function VoiceInput({
    onTranscript,
    onStart,
    onStop,
    onError,
    disabled = false,
    lang,
    size = 'default',
    className,
}: VoiceInputProps) {
    const reducedMotion = useReducedMotion();
    const [voiceState, setVoiceState] = React.useState<VoiceInputState>('idle');
    const recognitionRef = React.useRef<SpeechRecognitionLike | null>(null);

    const isSupported = React.useMemo(
        () =>
            typeof window !== 'undefined' &&
            ('SpeechRecognition' in window ||
                'webkitSpeechRecognition' in window),
        [],
    );

    const start = React.useCallback(() => {
        if (!isSupported) {
            onError?.('Speech recognition is not supported in this browser.');
            return;
        }

        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        const win = window as any;
        const SpeechRecognitionClass: new () => SpeechRecognitionLike =
            win.SpeechRecognition ?? win.webkitSpeechRecognition;

        if (!SpeechRecognitionClass) return;

        const recognition = new SpeechRecognitionClass();
        recognition.continuous = false;
        recognition.interimResults = false;
        if (lang) recognition.lang = lang;

        recognition.onstart = () => {
            setVoiceState('listening');
            onStart?.();
        };

        recognition.onresult = (event: {
            results: { transcript: string }[][];
        }) => {
            setVoiceState('processing');
            const transcript = event.results[0]?.[0]?.transcript ?? '';
            onTranscript?.(transcript);
            setVoiceState('idle');
        };

        recognition.onerror = (event: { error: string }) => {
            setVoiceState('error');
            onError?.(event.error);
            setTimeout(() => setVoiceState('idle'), 2000);
        };

        recognition.onend = () => {
            onStop?.();
            setVoiceState((prev) =>
                prev === 'listening' ? 'processing' : prev,
            );
        };

        recognitionRef.current = recognition;
        recognition.start();
    }, [isSupported, lang, onStart, onStop, onError, onTranscript]);

    const stop = React.useCallback(() => {
        recognitionRef.current?.stop();
        setVoiceState('idle');
    }, []);

    const toggle = React.useCallback(() => {
        if (voiceState === 'listening') {
            stop();
        } else {
            start();
        }
    }, [voiceState, start, stop]);

    // Cleanup on unmount
    React.useEffect(
        () => () => {
            recognitionRef.current?.stop();
        },
        [],
    );

    const isListening = voiceState === 'listening';
    const isProcessing = voiceState === 'processing';
    const isError = voiceState === 'error';

    const buttonSizeMap = {
        sm: 'icon-sm',
        default: 'icon',
        lg: 'icon-lg',
    } as const;

    return (
        <div
            className={cn(
                'relative inline-flex items-center justify-center',
                className,
            )}
        >
            {/* Pulse ring while listening */}
            {isListening && !reducedMotion && (
                <span
                    className="absolute inset-0 animate-ping rounded-full bg-error/20"
                    aria-hidden
                />
            )}
            <Button
                type="button"
                variant={isListening || isError ? 'soft' : 'outline'}
                color={isListening || isError ? 'error' : undefined}
                size={buttonSizeMap[size]}
                disabled={disabled || !isSupported || isProcessing}
                onClick={toggle}
                aria-label={
                    isListening ? 'Stop recording' : 'Start voice input'
                }
                aria-pressed={isListening}
                title={
                    !isSupported
                        ? 'Speech recognition not supported'
                        : undefined
                }
            >
                {isProcessing ? (
                    <Loader2Icon className="animate-spin" />
                ) : isListening ? (
                    <MicOffIcon />
                ) : (
                    <MicIcon />
                )}
            </Button>
        </div>
    );
}
