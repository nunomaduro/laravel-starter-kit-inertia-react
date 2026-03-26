# Laravel AI SDK

Comprehensive guide for building AI-powered features with the Laravel AI SDK (`laravel/ai`). 17 rules across 7 categories.

**Version:** 1.0.0

## Overview

This skill provides guidance for:
- Creating and configuring AI agents
- Building custom tools and using provider tools
- Generating images, audio, and transcriptions
- Creating and querying vector embeddings
- Managing files and vector stores for RAG
- Testing AI features with fakes and assertions

## Categories

### 1. Agents (Critical)
Create agents with instructions, tools, structured output, streaming, and middleware.

### 2. Tools (High)
Build custom tools with JSON schemas. Use provider tools like WebSearch, WebFetch, FileSearch.

### 3. Embeddings & Search (High)
Generate embeddings, cache them, query with pgvector, and rerank results.

### 4. Media Generation (Medium)
Generate images and audio. Transcribe speech to text.

### 5. Files & Storage (Medium)
Store files with AI providers. Create vector stores for retrieval-augmented generation.

### 6. Infrastructure (Medium)
Automatic provider failover for resilience.

### 7. Testing (High)
Fake agents, images, audio, embeddings, and vector stores. Assert prompts and generations.

## Rules

| Rule | Category | Impact |
|------|----------|--------|
| `agent-create-configure` | Agents | CRITICAL |
| `agent-prompting` | Agents | CRITICAL |
| `agent-structured-output` | Agents | HIGH |
| `agent-streaming-async` | Agents | HIGH |
| `agent-middleware` | Agents | MEDIUM |
| `agent-anonymous` | Agents | MEDIUM |
| `tool-create` | Tools | HIGH |
| `tool-provider` | Tools | HIGH |
| `embed-generate-cache` | Embeddings & Search | HIGH |
| `embed-rerank` | Embeddings & Search | MEDIUM |
| `media-images` | Media Generation | MEDIUM |
| `media-audio-transcription` | Media Generation | MEDIUM |
| `files-vector-stores` | Files & Storage | MEDIUM |
| `infra-failover` | Infrastructure | MEDIUM |
| `test-agents` | Testing | HIGH |
| `test-media` | Testing | HIGH |
| `test-data` | Testing | HIGH |
