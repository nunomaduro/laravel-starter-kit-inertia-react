import { RefObject, useEffect } from 'react';

const FOCUSABLE_SELECTORS = [
    'a[href]',
    'area[href]',
    'input:not([disabled])',
    'select:not([disabled])',
    'textarea:not([disabled])',
    'button:not([disabled])',
    '[tabindex]:not([tabindex="-1"])',
    'details > summary',
].join(', ');

/**
 * Traps keyboard focus within the given container element.
 * Intended for custom overlays and modals that don't use Radix UI primitives.
 *
 * @param containerRef - Ref to the element that should contain focus
 * @param enabled      - Whether the trap is active (default: true)
 */
export function useFocusTrap(containerRef: RefObject<HTMLElement | null>, enabled: boolean = true): void {
    useEffect(() => {
        if (!enabled || !containerRef.current) {
            return;
        }

        const container = containerRef.current;

        const getFocusableElements = (): HTMLElement[] =>
            Array.from(container.querySelectorAll<HTMLElement>(FOCUSABLE_SELECTORS)).filter(
                (el) => !el.closest('[hidden]') && el.getAttribute('aria-hidden') !== 'true',
            );

        const handleKeyDown = (event: KeyboardEvent) => {
            if (event.key !== 'Tab') {
                return;
            }

            const focusable = getFocusableElements();

            if (focusable.length === 0) {
                return;
            }

            const first = focusable[0];
            const last = focusable[focusable.length - 1];

            if (event.shiftKey) {
                if (document.activeElement === first) {
                    event.preventDefault();
                    last.focus();
                }
            } else {
                if (document.activeElement === last) {
                    event.preventDefault();
                    first.focus();
                }
            }
        };

        container.addEventListener('keydown', handleKeyDown);

        // Focus first focusable element on activation
        const focusable = getFocusableElements();

        if (focusable.length > 0) {
            focusable[0].focus();
        }

        return () => {
            container.removeEventListener('keydown', handleKeyDown);
        };
    }, [containerRef, enabled]);
}
