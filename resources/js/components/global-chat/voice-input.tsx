import { Mic, MicOff } from 'lucide-react';
import { useCallback, useEffect, useRef, useState } from 'react';

interface VoiceInputProps {
    onResult: (transcript: string) => void;
}

// Extend window for SpeechRecognition
interface SpeechRecognitionEvent extends Event {
    results: {
        length: number;
        item: (index: number) => {
            item: (index: number) => { transcript: string };
        };
        [index: number]: {
            [index: number]: { transcript: string };
            isFinal: boolean;
        };
    };
}

interface SpeechRecognitionInstance extends EventTarget {
    continuous: boolean;
    interimResults: boolean;
    lang: string;
    start: () => void;
    stop: () => void;
    abort: () => void;
    onresult: ((event: SpeechRecognitionEvent) => void) | null;
    onerror: ((event: Event & { error: string }) => void) | null;
    onend: (() => void) | null;
}

function getSpeechRecognition(): (new () => SpeechRecognitionInstance) | null {
    if (typeof window === 'undefined') return null;
    return (
        (window as unknown as Record<string, unknown>).SpeechRecognition ??
        (window as unknown as Record<string, unknown>).webkitSpeechRecognition
    ) as (new () => SpeechRecognitionInstance) | null ?? null;
}

export function VoiceInput({ onResult }: VoiceInputProps) {
    const [active, setActive] = useState(false);
    const [supported, setSupported] = useState(false);
    const recognitionRef = useRef<SpeechRecognitionInstance | null>(null);

    useEffect(() => {
        setSupported(!!getSpeechRecognition());
    }, []);

    const toggle = useCallback(() => {
        if (active) {
            recognitionRef.current?.stop();
            setActive(false);
            return;
        }

        const SpeechRecognition = getSpeechRecognition();
        if (!SpeechRecognition) return;

        const recognition = new SpeechRecognition();
        recognition.continuous = false;
        recognition.interimResults = false;
        recognition.lang = navigator.language || 'en-US';

        recognition.onresult = (event: SpeechRecognitionEvent) => {
            const result = event.results[0];
            if (result?.[0]?.transcript) {
                onResult(result[0].transcript);
            }
        };

        recognition.onerror = () => {
            setActive(false);
        };

        recognition.onend = () => {
            setActive(false);
        };

        recognitionRef.current = recognition;
        recognition.start();
        setActive(true);
    }, [active, onResult]);

    if (!supported) return null;

    return (
        <button
            type="button"
            onClick={toggle}
            className={`flex size-8 shrink-0 items-center justify-center rounded-lg transition-colors duration-100 ${
                active
                    ? 'bg-destructive text-white'
                    : 'text-muted-foreground hover:bg-muted hover:text-foreground'
            }`}
            aria-label={active ? 'Stop recording' : 'Voice input'}
            data-pan="global-chat-voice"
        >
            {active ? (
                <>
                    <MicOff className="size-3.5" />
                    <span className="absolute size-8 animate-ping rounded-lg bg-destructive/20" />
                </>
            ) : (
                <Mic className="size-3.5" />
            )}
        </button>
    );
}
