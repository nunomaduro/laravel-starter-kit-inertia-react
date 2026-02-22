# AI Chat API

The app exposes a streaming chat endpoint that uses Laravel AI and returns responses in the TanStack AG-UI NDJSON protocol so the frontend can use `useChat` from `@tanstack/ai-react` with `fetchHttpStream`. Conversations are persisted in `agent_conversations` and `agent_conversation_messages`; AI memory (store/recall) is scoped per user.

## Endpoints

### POST /api/chat

- **Auth**: `auth:sanctum` (session or Bearer token).

**Request** (JSON body):

- `messages`: required array of `{ role: 'user' | 'assistant' | 'system', content: string }`. The last user message is used as the prompt for the agent.
- `conversation_id`: optional UUID. When present, the message is appended to that conversation (must exist and belong to the authenticated user). When absent, a new conversation is created and its id is returned in the stream.

**Response**:

- **Content-Type**: `application/x-ndjson`
- **Body**: Newline-delimited JSON (NDJSON). Each line is an AG-UI event object:
  - `RUN_STARTED`: run id and timestamp
  - `CONVERSATION_CREATED`: when a new conversation was created; includes `conversationId` (UUID). Emitted once, immediately after `RUN_STARTED`, so the frontend can set the active conversation.
  - `TEXT_MESSAGE_START`: message id, role assistant
  - `TEXT_MESSAGE_CONTENT`: message id, delta, content (accumulated)
  - `TEXT_MESSAGE_END`: message id
  - `RUN_FINISHED`: run id, finishReason, usage (promptTokens, completionTokens, totalTokens)
  - `RUN_ERROR`: run id, error { message, code } on failure

### GET /api/conversations

- **Auth**: `auth:sanctum`
- **Query**: `per_page` (optional, 1–50, default 20).
- **Response**: JSON `{ data: [{ id, title, created_at, updated_at }], meta: { current_page, last_page, per_page, total } }`. Conversations for the current user, ordered by `updated_at` desc.

### GET /api/conversations/{id}

- **Auth**: `auth:sanctum`
- **Response**: JSON `{ data: { id, title, created_at, updated_at, messages: [{ id, role, content }] } }`. Single conversation and its messages for the current user; 404 if not found or not owned.

### GET /api/chat/memories

- **Auth**: `auth:sanctum`
- **Query**: `limit` (optional, 1–50, default 20).
- **Response**: JSON `{ data: [{ id, content }] }`. Read-only list of the current user’s AI memories (from Laravel AI Memory), for display in the chat UI.

## Implementation

- **ChatController**: `App\Http\Controllers\Api\ChatController` (invokable). Requires auth; validates `messages` and optional `conversation_id` (UUID, must belong to user). Creates a new row in `agent_conversations` when no `conversation_id` is sent; builds `AssistantAgent::make(['user_id' => $user->id])->continue($conversationId, $user)` so conversation and memory are user-scoped; streams and emits `CONVERSATION_CREATED` in the first chunk when a new conversation was created.
- **ConversationController**: `App\Http\Controllers\Api\ConversationController` — `index()` and `show($id)` for listing and loading conversations.
- **ChatMemoryController**: `App\Http\Controllers\Api\ChatMemoryController` (invokable) — returns `AgentMemory::all(['user_id' => $user->id])` for the memories endpoint.
