---
name: durable-workflow
description: >-
  Durable Workflow (laravel-workflow) and Waterline. Activates when defining
  workflows or activities, using WorkflowStub, monitoring workflows at
  /waterline, or when the user mentions durable workflow, Waterline,
  long-running workflows, sagas, or workflow orchestration.
license: MIT
metadata:
  author: project
---

# Durable Workflow & Waterline

## When to Activate

Activate when:

- Defining or editing **workflow** classes (extend `Workflow\Workflow`)
- Defining or editing **activity** classes (extend `Workflow\Activity`)
- Using **WorkflowStub** to start or await workflows
- Configuring or using the **Waterline** dashboard at `/waterline`
- User mentions: durable workflow, Waterline, long-running workflows, sagas, workflow orchestration, multi-step processes, compensation

## Packages

- **laravel-workflow/laravel-workflow** — Durable Workflow engine (workflows + activities, runs on Laravel queues).
- **laravel-workflow/waterline** — Monitoring UI for workflow runs (similar to Horizon for jobs).

## Key Concepts

- **Workflow**: Orchestration class with `execute()` that `yield`s activities. Survives restarts; can run for minutes/hours/days.
- **Activity**: Single unit of work (one class per task). Run via `yield activity(MyActivity::class, ...args)`.
- **WorkflowStub**: `WorkflowStub::make(MyWorkflow::class)->start($arg)`. Use `->output()` to wait for result (or poll/events).

## Configuration

- **Waterline**: `config/waterline.php` — path (default `waterline`), domain, middleware.
- **Gate**: `viewWaterline` in `WaterlineServiceProvider::gate()` — same as Horizon: `can('access admin panel')`.
- **Queue**: Workflows use the same Laravel queue as jobs; run workers with Horizon or `queue:work`.

## Artisan

- `php artisan make:workflow {Name}` — create a workflow class.
- `php artisan waterline:install` — install Waterline (already run in this project).
- `php artisan waterline:publish` — republish assets after upgrading.

## Documentation

- Full guide: `docs/developer/backend/durable-workflow.md`
- Backend at-a-glance: `docs/developer/backend/README.md` (Durable Workflow & Waterline bullet)
- [Durable Workflow docs](https://durable-workflow.com/docs/introduction/)
- [GitHub: workflow](https://github.com/durable-workflow/workflow), [waterline](https://github.com/durable-workflow/waterline)
