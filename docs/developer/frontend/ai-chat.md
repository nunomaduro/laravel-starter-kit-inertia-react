# AI Chat (TanStack AI + Laravel AI)

The chat UI uses **@tanstack/ai-react** `useChat` with a **fetchHttpStream** connection to the Laravel `/api/chat` endpoint, which streams NDJSON in the AG-UI protocol. Conversations are persisted; the page shows a conversation list and loads messages by conversation id.

## Assistant UI (modal / overlay)

The **Assistant Modal** (`AssistantModal`, used for on-demand overlays) is powered by **@assistant-ui/react** and a **Laravel chat adapter** instead of TanStack useChat:

- **Adapter**: `resources/js/components/ai/laravel-chat-adapter.ts` — `createLaravelChatAdapter({ endpoint, model?, headers?, systemPrompt? })` returns a runtime adapter that POSTs to the given Laravel streaming endpoint (SSE or JSON), maps messages to the format the backend expects, and yields assistant replies (streamed or single) into assistant-ui’s thread.
- **Thread**: `resources/js/components/ai/assistant-thread-aui.tsx` — `AssistantThreadAui` wraps assistant-ui’s `ThreadPrimitive.Root`, `ThreadPrimitive.Messages`, and `ComposerPrimitive` with `AssistantRuntimeProvider` and `useLocalRuntime(createLaravelChatAdapter(...))`.
- **Modal**: `resources/js/components/ai/assistant-modal.tsx` — Renders a dialog that uses `AssistantThreadAui` with the same props (`endpoint`, `model`, `headers`, `systemPrompt`, `placeholder`). The full chat page (`/chat`) still uses the custom TanStack-based implementation; only the modal (and optionally the sidebar) use assistant-ui.

## Page

- **Route**: GET `/chat` (name: `chat`). Optional query `?conversation={id}` to open a specific conversation.
- **Layout**: `AppSidebarLayout`; sidebar includes a “Chat” nav item.
- **File**: `resources/js/pages/chat/index.tsx`.

## Connection

The page creates a connection with:

- **URL**: `/api/chat`
- **Options**: `credentials: 'include'`, `Accept: application/x-ndjson`, `Content-Type: application/json`, `X-Requested-With: XMLHttpRequest`, and **body**: `{ conversation_id }` when the user is in an existing conversation (so the backend appends to that conversation).

The backend returns NDJSON (AG-UI events); the TanStack client parses them and updates `messages` and status.

## Conversation list and load

- **Sidebar**: Calls GET `/api/conversations` and shows conversation titles and dates. “New chat” clears the active conversation and messages; clicking a row navigates to `/chat?conversation={id}` and loads that conversation’s messages.
- **Loading a conversation**: When `?conversation={id}` is present, the page fetches GET `/api/conversations/{id}` and maps the response messages to UIMessage format (`id`, `role`, `parts: [{ type: 'text', content }]`), then calls `setMessages(serverMessages)` so the thread shows persisted messages.
- **Sending with conversation_id**: When the user is in an existing conversation, the request body includes `conversation_id` so the backend appends to that conversation. When starting a new chat, `conversation_id` is omitted; the backend creates a conversation and emits `CONVERSATION_CREATED` in the first chunk; the frontend handles it in `onChunk`, updates the active conversation id and URL (`/chat?conversation={id}`), so subsequent sends include that id.

## Memories (optional)

- A “What I remember” control in the sidebar calls GET `/api/chat/memories` and displays the returned list of memory snippets (read-only), showing that the assistant’s AI memory is persisted and user-scoped.

## Usage

- **sendMessage(content)**: Sends a string (or multimodal content) and appends the assistant reply as it streams.
- **setMessages(messages)**: Used to hydrate the thread from GET `/api/conversations/{id}` when a conversation is selected.
- **stop()**: Stops the current stream.
- **messages**, **isLoading**, **error**, **status**: React state from `useChat`.

See backend [ai-chat.md](../backend/ai-chat.md) for the API contract.
