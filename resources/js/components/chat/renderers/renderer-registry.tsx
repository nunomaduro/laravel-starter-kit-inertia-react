import type { ComponentType } from 'react';

export interface RendererProps {
    data: Record<string, unknown>;
}

type RendererMap = Map<string, ComponentType<RendererProps>>;

const registry: RendererMap = new Map();

export function registerRenderer(
    type: string,
    component: ComponentType<RendererProps>,
): void {
    registry.set(type, component);
}

export function getRenderer(
    type: string,
): ComponentType<RendererProps> | undefined {
    return registry.get(type);
}

export function renderBlock(
    type: string,
    data: Record<string, unknown>,
    key?: string,
): JSX.Element | null {
    const Renderer = registry.get(type);
    if (!Renderer) return null;
    return <Renderer key={key} data={data} />;
}

export function getRegisteredTypes(): string[] {
    return [...registry.keys()];
}
