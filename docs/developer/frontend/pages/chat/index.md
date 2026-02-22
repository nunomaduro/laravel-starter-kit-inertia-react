# Chat (AI)

## Purpose

Allows authenticated users to have a streaming conversation with the Laravel AI agent. Messages are sent via `POST /api/chat` and the UI uses TanStack AI `useChat` with NDJSON streaming.

## Location

`resources/js/pages/chat/index.tsx`

## Route Information

- **URL**: `/chat`
- **Route Name**: `chat`
- **HTTP Method**: GET
- **Middleware**: web, auth, verified

## Props (from Controller)

| Prop | Type | Description |
|------|------|-------------|
| (none) | - | Page is client-driven; chat state and messages are managed by TanStack AI and the streaming API. |

## User Flow

1. User navigates to `/chat` (or selects Chat from sidebar/command palette).
2. User types a message and submits.
3. The app sends the message to `POST /api/chat`; the response is an NDJSON stream.
4. TanStack AI `useChat` appends assistant content as it streams; the user sees the reply in real time.

## Related Components

- **Backend**: `App\Http\Controllers\Api\ChatController` (invokable); streams via Laravel AI agent. See `docs/developer/backend/ai-chat.md`.
- **Route**: `chat` (GET /chat), `POST /api/chat` (API, auth:sanctum).

## Implementation Details

- Uses `useChat` from `@tanstack/ai-react` with a custom `fetchHttpStream` connection pointing at `/api/chat`.
- Auth token (e.g. Sanctum) is sent in the request headers for the streaming request.
- See `docs/developer/frontend/ai-chat.md` for frontend patterns.
