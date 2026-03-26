/**
 * Bot Studio Embed Widget
 *
 * Standalone chat widget using shadow DOM for style isolation.
 * No React, no external dependencies.
 */

import { getStyles } from './embed-styles';

interface EmbedConfig {
    token: string;
    baseUrl: string;
    mode?: 'widget' | 'standalone';
    agent: {
        name: string;
        slug: string;
        avatar_url: string | null;
        description: string | null;
    };
    theme: {
        primary_color: string;
        position: 'bottom-right' | 'bottom-left';
        greeting: string;
        placeholder: string;
        show_powered_by: boolean;
    };
    conversation_starters: string[];
}

interface ChatMessage {
    id: string;
    role: 'user' | 'assistant';
    content: string;
}

const BOT_ICON = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 8V4H8"/><rect width="16" height="12" x="4" y="8" rx="2"/><path d="M2 14h2"/><path d="M20 14h2"/><path d="M15 13v2"/><path d="M9 13v2"/></svg>`;
const CLOSE_ICON = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M18 6L6 18"/><path d="M6 6l12 12"/></svg>`;
const SEND_ICON = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>`;
const CHAT_ICON = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/></svg>`;

export class EmbedWidget {
    private config: EmbedConfig;
    private shadow: ShadowRoot;
    private messages: ChatMessage[] = [];
    private isOpen = false;
    private isStreaming = false;
    private abortController: AbortController | null = null;

    private panel!: HTMLDivElement;
    private messagesContainer!: HTMLDivElement;
    private inputField!: HTMLTextAreaElement;
    private sendBtn!: HTMLButtonElement;
    private fab!: HTMLButtonElement | null;

    constructor(config: EmbedConfig, hostElement: HTMLElement) {
        this.config = config;
        this.shadow = hostElement.attachShadow({ mode: 'open' });

        // Load persisted messages
        this.loadMessages();

        // Inject styles
        const style = document.createElement('style');
        style.textContent = getStyles(config.theme.primary_color);
        this.shadow.appendChild(style);

        // Build UI
        if (config.mode === 'standalone') {
            this.buildStandaloneUI();
        } else {
            this.buildWidgetUI();
        }
    }

    private buildWidgetUI(): void {
        // Floating action button
        this.fab = document.createElement('button');
        this.fab.className = `embed-fab ${this.config.theme.position}`;
        this.fab.innerHTML = CHAT_ICON;
        this.fab.addEventListener('click', () => this.toggle());
        this.shadow.appendChild(this.fab);

        // Chat panel
        this.panel = document.createElement('div');
        this.panel.className = `embed-panel ${this.config.theme.position} hidden`;
        this.buildPanelContents(true);
        this.shadow.appendChild(this.panel);
    }

    private buildStandaloneUI(): void {
        this.fab = null;

        this.panel = document.createElement('div');
        this.panel.className = 'embed-panel standalone';
        this.buildPanelContents(false);
        this.shadow.appendChild(this.panel);

        // Auto-open in standalone mode
        this.isOpen = true;
    }

    private buildPanelContents(showHeader: boolean): void {
        if (showHeader) {
            const header = document.createElement('div');
            header.className = 'panel-header';
            header.innerHTML = `
                <div class="panel-header-left">
                    <div class="panel-avatar">
                        ${this.config.agent.avatar_url
                            ? `<img src="${this.escapeHtml(this.config.agent.avatar_url)}" alt="${this.escapeHtml(this.config.agent.name)}">`
                            : BOT_ICON
                        }
                    </div>
                    <span class="panel-name">${this.escapeHtml(this.config.agent.name)}</span>
                </div>
                <button class="panel-close">${CLOSE_ICON}</button>
            `;
            header.querySelector('.panel-close')!.addEventListener('click', () => this.close());
            this.panel.appendChild(header);
        }

        // Messages area
        this.messagesContainer = document.createElement('div');
        this.messagesContainer.className = 'messages';
        this.panel.appendChild(this.messagesContainer);

        // Input area
        const inputArea = document.createElement('div');
        inputArea.className = 'input-area';

        this.inputField = document.createElement('textarea');
        this.inputField.className = 'input-field';
        this.inputField.placeholder = this.config.theme.placeholder;
        this.inputField.rows = 1;
        this.inputField.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendMessage();
            }
        });
        this.inputField.addEventListener('input', () => {
            // Auto-resize textarea
            this.inputField.style.height = 'auto';
            this.inputField.style.height = Math.min(this.inputField.scrollHeight, 120) + 'px';
        });

        this.sendBtn = document.createElement('button');
        this.sendBtn.className = 'send-btn';
        this.sendBtn.innerHTML = SEND_ICON;
        this.sendBtn.addEventListener('click', () => this.sendMessage());

        inputArea.appendChild(this.inputField);
        inputArea.appendChild(this.sendBtn);
        this.panel.appendChild(inputArea);

        // Powered by
        if (this.config.theme.show_powered_by) {
            const powered = document.createElement('div');
            powered.className = 'powered-by';
            powered.innerHTML = `Powered by <a href="${this.escapeHtml(this.config.baseUrl)}" target="_blank" rel="noopener">Bot Studio</a>`;
            this.panel.appendChild(powered);
        }

        // Render initial state
        this.renderMessages();
    }

    private renderMessages(): void {
        this.messagesContainer.innerHTML = '';

        if (this.messages.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'empty-state';
            empty.innerHTML = `
                <div class="empty-avatar">
                    ${this.config.agent.avatar_url
                        ? `<img src="${this.escapeHtml(this.config.agent.avatar_url)}" alt="${this.escapeHtml(this.config.agent.name)}">`
                        : BOT_ICON
                    }
                </div>
                <div class="empty-greeting">${this.escapeHtml(this.config.theme.greeting)}</div>
            `;

            if (this.config.conversation_starters.length > 0) {
                const starters = document.createElement('div');
                starters.className = 'starters';
                this.config.conversation_starters.forEach((starter) => {
                    if (!starter.trim()) return;
                    const chip = document.createElement('button');
                    chip.className = 'starter-chip';
                    chip.textContent = starter;
                    chip.addEventListener('click', () => {
                        this.inputField.value = starter;
                        this.sendMessage();
                    });
                    starters.appendChild(chip);
                });
                empty.appendChild(starters);
            }

            this.messagesContainer.appendChild(empty);
            return;
        }

        this.messages.forEach((msg) => {
            const msgEl = document.createElement('div');
            msgEl.className = `msg ${msg.role}`;
            msgEl.setAttribute('data-id', msg.id);

            const bubble = document.createElement('div');
            bubble.className = 'msg-bubble';
            bubble.textContent = msg.content;

            if (msg.role === 'assistant' && msg.content === '' && this.isStreaming) {
                const dot = document.createElement('span');
                dot.className = 'typing-dot';
                bubble.appendChild(dot);
            }

            msgEl.appendChild(bubble);
            this.messagesContainer.appendChild(msgEl);
        });

        this.scrollToBottom();
    }

    private updateLastAssistantMessage(content: string): void {
        const lastMsg = this.messages[this.messages.length - 1];
        if (!lastMsg || lastMsg.role !== 'assistant') return;

        lastMsg.content = content;

        // Find the bubble in DOM and update directly for performance
        const msgEl = this.messagesContainer.querySelector(`[data-id="${lastMsg.id}"]`);
        if (msgEl) {
            const bubble = msgEl.querySelector('.msg-bubble');
            if (bubble) {
                bubble.textContent = content;
            }
        }

        this.scrollToBottom();
    }

    private scrollToBottom(): void {
        requestAnimationFrame(() => {
            this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
        });
    }

    toggle(): void {
        if (this.isOpen) {
            this.close();
        } else {
            this.open();
        }
    }

    open(): void {
        this.isOpen = true;
        this.panel.classList.remove('hidden');
        if (this.fab) {
            this.fab.innerHTML = CLOSE_ICON;
        }
        requestAnimationFrame(() => this.inputField.focus());
    }

    close(): void {
        this.isOpen = false;
        this.panel.classList.add('hidden');
        if (this.fab) {
            this.fab.innerHTML = CHAT_ICON;
        }
    }

    private async sendMessage(): Promise<void> {
        const text = this.inputField.value.trim();
        if (!text || this.isStreaming) return;

        const userMsg: ChatMessage = {
            id: this.generateId(),
            role: 'user',
            content: text,
        };

        const assistantMsg: ChatMessage = {
            id: this.generateId(),
            role: 'assistant',
            content: '',
        };

        this.messages.push(userMsg, assistantMsg);
        this.inputField.value = '';
        this.inputField.style.height = 'auto';
        this.isStreaming = true;
        this.sendBtn.disabled = true;
        this.renderMessages();

        this.abortController = new AbortController();

        try {
            const url = `${this.config.baseUrl}/api/embed/${this.config.token}/chat`;
            const res = await fetch(url, {
                method: 'POST',
                signal: this.abortController.signal,
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/x-ndjson',
                },
                body: JSON.stringify({
                    message: text,
                }),
            });

            if (!res.ok) {
                const data = await res.json().catch(() => ({ message: 'Chat request failed.' }));
                assistantMsg.content = data.message || 'Something went wrong. Please try again.';
                this.renderMessages();
                return;
            }

            if (!res.body) {
                assistantMsg.content = 'No response received.';
                this.renderMessages();
                return;
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
                            delta?: string;
                            content?: string;
                            error?: { message?: string };
                        };

                        if (chunk.type === 'TEXT_MESSAGE_CONTENT' && chunk.delta) {
                            this.updateLastAssistantMessage(
                                assistantMsg.content + chunk.delta,
                            );
                            assistantMsg.content += chunk.delta;
                        }

                        if (chunk.type === 'RUN_ERROR' && chunk.error?.message) {
                            assistantMsg.content = 'Error: ' + chunk.error.message;
                            this.renderMessages();
                        }
                    } catch {
                        // Skip malformed lines
                    }
                }
            }
        } catch (err) {
            if (err instanceof DOMException && err.name === 'AbortError') {
                return;
            }
            if (!assistantMsg.content) {
                assistantMsg.content = 'Connection failed. Please try again.';
                this.renderMessages();
            }
        } finally {
            this.isStreaming = false;
            this.sendBtn.disabled = false;
            this.abortController = null;
            this.saveMessages();
            this.scrollToBottom();
        }
    }

    private generateId(): string {
        return Math.random().toString(36).substring(2) + Date.now().toString(36);
    }

    private escapeHtml(text: string): string {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    private get storageKey(): string {
        return `bot-studio-embed-${this.config.token}`;
    }

    private saveMessages(): void {
        try {
            const data = this.messages.filter((m) => m.content.trim() !== '');
            localStorage.setItem(this.storageKey, JSON.stringify(data));
        } catch {
            // localStorage may be unavailable (privacy mode, quota exceeded)
        }
    }

    private loadMessages(): void {
        try {
            const raw = localStorage.getItem(this.storageKey);
            if (raw) {
                this.messages = JSON.parse(raw) as ChatMessage[];
            }
        } catch {
            this.messages = [];
        }
    }
}
