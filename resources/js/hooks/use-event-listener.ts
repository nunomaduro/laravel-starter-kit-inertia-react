import { type RefObject, useEffect, useRef } from 'react';

type Target = EventTarget | RefObject<EventTarget | null> | null;

export function useEventListener<K extends keyof WindowEventMap>(
    eventName: K,
    handler: (event: WindowEventMap[K]) => void,
    element?: undefined,
): void;
export function useEventListener<
    K extends keyof HTMLElementEventMap,
    T extends HTMLElement,
>(
    eventName: K,
    handler: (event: HTMLElementEventMap[K]) => void,
    element: RefObject<T | null>,
): void;
export function useEventListener<K extends string>(
    eventName: K,
    handler: (event: Event) => void,
    element?: Target,
): void {
    const savedHandlerRef = useRef(handler);
    savedHandlerRef.current = handler;

    useEffect(() => {
        const target: EventTarget | null = element
            ? 'current' in (element as RefObject<EventTarget | null>)
                ? (element as RefObject<EventTarget | null>).current
                : (element as EventTarget)
            : window;

        if (!target) {
            return;
        }

        const listener = (event: Event) => savedHandlerRef.current(event);
        target.addEventListener(eventName, listener);

        return () => target.removeEventListener(eventName, listener);
    }, [eventName, element]);
}
