# Laravel MCP

Comprehensive guide for building MCP (Model Context Protocol) servers with Laravel. 7 rules across 5 categories.

**Version:** 1.0.0

## Overview

This skill provides guidance for:
- Creating and registering MCP servers (web and local)
- Building tools with input/output schemas and responses
- Defining prompts with arguments and validation
- Exposing resources and resource templates
- Protecting servers with OAuth 2.1, Sanctum, or custom auth
- Testing with MCP Inspector and unit tests

## Categories

### 1. Servers (Critical)
Create and register MCP servers for web (HTTP) and local (Artisan CLI) transports.

### 2. Tools (High)
Build tools with JSON schemas, validation, dependency injection, annotations, and multiple response types.

### 3. Prompts & Resources (Medium)
Define reusable prompt templates and expose data resources for AI client context.

### 4. Authentication (High)
Protect MCP servers with OAuth 2.1 (Passport), Sanctum tokens, or custom middleware.

### 5. Testing (High)
Test servers with MCP Inspector and write unit tests with assertions.

## Rules

| Rule | Category | Impact |
|------|----------|--------|
| `server-create-register` | Servers | CRITICAL |
| `tool-create` | Tools | HIGH |
| `tool-responses` | Tools | HIGH |
| `prompt-create` | Prompts & Resources | MEDIUM |
| `resource-create` | Prompts & Resources | MEDIUM |
| `auth-protect` | Authentication | HIGH |
| `test-unit` | Testing | HIGH |
