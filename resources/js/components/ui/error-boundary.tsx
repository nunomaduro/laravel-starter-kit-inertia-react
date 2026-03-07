import { Component, type ErrorInfo, type ReactNode } from 'react';

import { ErrorState } from '@/components/ui/error-state';

type FallbackRender = (error: Error, reset: () => void) => ReactNode;

interface ErrorBoundaryProps {
    children: ReactNode;
    /** Custom fallback UI. Accepts a ReactNode or a render-prop `(error, reset) => ReactNode`. */
    fallback?: ReactNode | FallbackRender;
}

interface ErrorBoundaryState {
    hasError: boolean;
    error: Error | null;
}

export class ErrorBoundary extends Component<ErrorBoundaryProps, ErrorBoundaryState> {
    constructor(props: ErrorBoundaryProps) {
        super(props);
        this.state = { hasError: false, error: null };
    }

    static getDerivedStateFromError(error: Error): ErrorBoundaryState {
        return { hasError: true, error };
    }

    componentDidCatch(error: Error, info: ErrorInfo): void {
        if (import.meta.env.DEV) {
            console.error('[ErrorBoundary]', error, info.componentStack);
        }
    }

    reset = (): void => {
        this.setState({ hasError: false, error: null });
    };

    render(): ReactNode {
        if (!this.state.hasError || !this.state.error) {
            return this.props.children;
        }

        const { fallback } = this.props;

        if (typeof fallback === 'function') {
            return (fallback as FallbackRender)(this.state.error, this.reset);
        }

        if (fallback !== undefined) {
            return fallback;
        }

        return <ErrorState error={this.state.error} onRetry={this.reset} bordered />;
    }
}
