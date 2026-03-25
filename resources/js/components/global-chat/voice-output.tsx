import { Volume2, VolumeX } from 'lucide-react';
import { useCallback, useEffect, useRef, useState } from 'react';

interface VoiceOutputProps {
    text: string;
}

export function VoiceOutput({ text }: VoiceOutputProps) {
    const [playing, setPlaying] = useState(false);
    const [supported, setSupported] = useState(false);
    const utteranceRef = useRef<SpeechSynthesisUtterance | null>(null);

    useEffect(() => {
        setSupported(typeof window !== 'undefined' && 'speechSynthesis' in window);
    }, []);

    const toggle = useCallback(() => {
        if (!supported) return;

        if (playing) {
            window.speechSynthesis.cancel();
            setPlaying(false);
            return;
        }

        const utterance = new SpeechSynthesisUtterance(text);
        utterance.rate = 1;
        utterance.pitch = 1;

        utterance.onend = () => setPlaying(false);
        utterance.onerror = () => setPlaying(false);

        utteranceRef.current = utterance;
        window.speechSynthesis.speak(utterance);
        setPlaying(true);
    }, [text, playing, supported]);

    // Cleanup on unmount
    useEffect(() => {
        return () => {
            if (playing) window.speechSynthesis.cancel();
        };
    }, [playing]);

    if (!supported || !text) return null;

    return (
        <button
            type="button"
            onClick={toggle}
            className="rounded p-0.5 text-muted-foreground opacity-0 transition-all duration-100 hover:text-foreground group-hover:opacity-100"
            aria-label={playing ? 'Stop speaking' : 'Read aloud'}
        >
            {playing ? (
                <VolumeX className="size-3" />
            ) : (
                <Volume2 className="size-3" />
            )}
        </button>
    );
}
