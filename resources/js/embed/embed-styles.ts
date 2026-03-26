/**
 * CSS-in-JS styles for the embed widget shadow DOM.
 * All styles are scoped within the shadow root for isolation.
 */

export function getStyles(primaryColor: string): string {
    return `
        :host {
            all: initial;
            font-family: 'IBM Plex Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        .embed-fab {
            position: fixed;
            z-index: 2147483647;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: ${primaryColor};
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .embed-fab:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 16px rgba(0,0,0,0.4);
        }

        .embed-fab.bottom-right {
            bottom: 20px;
            right: 20px;
        }

        .embed-fab.bottom-left {
            bottom: 20px;
            left: 20px;
        }

        .embed-fab svg {
            width: 24px;
            height: 24px;
            color: white;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .embed-panel {
            position: fixed;
            z-index: 2147483646;
            width: 400px;
            height: 600px;
            max-height: calc(100vh - 100px);
            max-width: calc(100vw - 40px);
            border-radius: 12px;
            background: #0a0a0a;
            border: 1px solid #262626;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0,0,0,0.5);
            transition: opacity 0.2s ease, transform 0.2s ease;
        }

        .embed-panel.bottom-right {
            bottom: 88px;
            right: 20px;
        }

        .embed-panel.bottom-left {
            bottom: 88px;
            left: 20px;
        }

        .embed-panel.standalone {
            position: relative;
            width: 100%;
            height: 100%;
            max-height: 100%;
            max-width: 100%;
            border-radius: 0;
            border: none;
            box-shadow: none;
        }

        .embed-panel.hidden {
            display: none;
        }

        .panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            border-bottom: 1px solid #262626;
            background: #141414;
        }

        .panel-header-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .panel-avatar {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: #262626;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .panel-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .panel-avatar svg {
            width: 18px;
            height: 18px;
            color: #a1a1aa;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .panel-name {
            font-family: 'JetBrains Mono', 'SF Mono', monospace;
            font-weight: 700;
            font-size: 13px;
            color: #fafafa;
            letter-spacing: -0.02em;
        }

        .panel-close {
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #a1a1aa;
            transition: color 0.1s, background 0.1s;
        }

        .panel-close:hover {
            color: #fafafa;
            background: #262626;
        }

        .panel-close svg {
            width: 18px;
            height: 18px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .messages {
            flex: 1;
            overflow-y: auto;
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .messages::-webkit-scrollbar {
            width: 4px;
        }

        .messages::-webkit-scrollbar-track {
            background: transparent;
        }

        .messages::-webkit-scrollbar-thumb {
            background: #262626;
            border-radius: 2px;
        }

        .empty-state {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 16px;
            padding: 24px;
        }

        .empty-avatar {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: #141414;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .empty-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .empty-avatar svg {
            width: 24px;
            height: 24px;
            color: #a1a1aa;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .empty-greeting {
            font-size: 14px;
            color: #a1a1aa;
            text-align: center;
            line-height: 1.5;
        }

        .starters {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 8px;
            max-width: 320px;
        }

        .starter-chip {
            background: none;
            border: 1px solid #262626;
            border-radius: 999px;
            padding: 6px 14px;
            font-size: 12px;
            color: #a1a1aa;
            cursor: pointer;
            font-family: inherit;
            transition: border-color 0.1s, color 0.1s;
        }

        .starter-chip:hover {
            border-color: ${primaryColor};
            color: #fafafa;
        }

        .msg {
            display: flex;
            max-width: 85%;
        }

        .msg.user {
            align-self: flex-end;
        }

        .msg.assistant {
            align-self: flex-start;
        }

        .msg-bubble {
            padding: 10px 14px;
            border-radius: 12px;
            font-size: 14px;
            line-height: 1.5;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .msg.user .msg-bubble {
            background: ${primaryColor};
            color: white;
            border-bottom-right-radius: 4px;
        }

        .msg.assistant .msg-bubble {
            background: #141414;
            color: #fafafa;
            border-bottom-left-radius: 4px;
        }

        .typing-dot {
            display: inline-block;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
            margin-left: 2px;
            animation: typing-pulse 1.2s ease-in-out infinite;
        }

        @keyframes typing-pulse {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 1; }
        }

        .input-area {
            padding: 12px 16px;
            border-top: 1px solid #262626;
            background: #141414;
            display: flex;
            gap: 8px;
            align-items: flex-end;
        }

        .input-field {
            flex: 1;
            background: #0a0a0a;
            border: 1px solid #262626;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 14px;
            color: #fafafa;
            font-family: inherit;
            resize: none;
            min-height: 40px;
            max-height: 120px;
            line-height: 1.4;
            outline: none;
            transition: border-color 0.1s;
        }

        .input-field::placeholder {
            color: #52525b;
        }

        .input-field:focus {
            border-color: ${primaryColor};
        }

        .send-btn {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: ${primaryColor};
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: opacity 0.1s;
        }

        .send-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        .send-btn svg {
            width: 18px;
            height: 18px;
            color: white;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .powered-by {
            padding: 8px 16px;
            text-align: center;
            font-size: 11px;
            color: #52525b;
            background: #141414;
        }

        .powered-by a {
            color: #71717a;
            text-decoration: none;
        }

        .powered-by a:hover {
            color: #a1a1aa;
        }
    `;
}
