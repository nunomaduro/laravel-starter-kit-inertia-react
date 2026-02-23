# Architecture Decision Records (ADRs)

This folder contains Architecture Decision Records: short documents that capture an important architectural decision made along with its context and consequences.

## Format

Each ADR uses the following structure:

- **Title** – Short descriptive name
- **Status** – e.g. `Accepted`, `Proposed`, `Deprecated`, `Superseded by [ADR-XXX]`
- **Context** – The issue or situation motivating the decision
- **Decision** – The change or approach we decided on
- **Consequences** – What becomes easier or harder as a result

## Naming

Use `ADR-NNN-short-slug.md` (e.g. `ADR-001-actions-for-business-logic.md`). Number sequentially.

## Index

| ADR | Title | Status |
|-----|-------|--------|
| [ADR-001](./ADR-001-actions-for-business-logic.md) | Use Action classes for business logic | Accepted |
| [ADR-002](./ADR-002-db-backed-settings-overlay.md) | DB-backed settings with config overlay and per-org overrides | Accepted |
