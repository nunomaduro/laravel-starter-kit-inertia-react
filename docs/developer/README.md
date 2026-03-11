# Developer Documentation

This documentation is for developers working on or extending this application.

## Table of Contents

- [Architecture](./architecture/overview.md) - High-level application architecture
- [Backend](./backend/README.md) - Backend components and patterns
- [Database](./backend/database/README.md) - Database patterns, seeders, and factories
- [Frontend](./frontend/README.md) - Frontend components and patterns
- [Deployment](./deployment.md) - Environment, assets, caching, and hardening

## Verify your setup

From the project root: `php artisan app:health && composer test` (health check + fast test suite). See [CONTRIBUTING.md](../../CONTRIBUTING.md) in the repo root for full setup and commands.

## Quick Links

- [Actions Documentation](./backend/actions/README.md) - All Action classes
- [Seeder System](./backend/database/seeders.md) - Automated seeder system
- [Prism AI Integration](./backend/prism.md) - AI-powered features with Prism
- [Laravel AI SDK](./backend/ai-sdk.md) - Agents, embeddings, images; when to use vs Prism
- [API Reference](./api-reference/README.md) - Routes and endpoints
