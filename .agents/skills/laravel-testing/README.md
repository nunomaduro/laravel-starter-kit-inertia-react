# Laravel 13 Testing — Pest PHP 4 & PHPUnit 12

Comprehensive testing guide for Laravel 13 applications. Supports both **Pest PHP 4** and **PHPUnit 12**. 24 rules across 6 categories.

## Framework Detection

Before applying rules, the skill detects which framework is in use:
1. Checks `composer.json` for `pestphp/pest` (Pest) or `phpunit/phpunit` alone (PHPUnit)
2. Checks if `tests/Pest.php` exists → Pest
3. If unclear → asks the user to choose Pest or PHPUnit

**Version:** 1.1.0

## Overview

This skill provides guidance for:
- HTTP feature tests and response assertions
- Model factories, states, and relationships
- Database assertions after operations
- Faking Mail, Queue, Notification, and Event facades
- Testing authenticated routes with actingAs and Sanctum
- Pest PHP patterns: describe/it, datasets, hooks

## Categories

### 1. HTTP & Feature Tests (Critical)
Structure feature tests with Arrange/Act/Assert. Use HTTP assertion methods to validate responses.

### 2. Model Factories (Critical)
Create test data with factories. Use states for scenarios, sequences for varied records, relationship helpers for related data.

### 3. Database Assertions (High)
Assert database state with assertDatabaseHas, assertDatabaseMissing, and assertSoftDeleted.

### 4. Faking Services (High)
Use Mail::fake(), Queue::fake(), Notification::fake(), Event::fake(), and Storage::fake() to prevent real side effects and assert behavior.

### 5. Authentication Testing (High)
Use actingAs() for session-based tests and Sanctum::actingAs() for API token tests.

### 6. Test Organisation Patterns (Medium)
Pest: describe/it blocks, datasets, beforeEach/afterEach hooks.
PHPUnit: test class organisation, #[DataProvider], setUp/tearDown.

## Rules

| Rule | Category | Impact |
|------|----------|--------|
| `http-test-structure` | HTTP & Feature Tests | CRITICAL |
| `http-assert-response` | HTTP & Feature Tests | CRITICAL |
| `http-assert-json-fluent` | HTTP & Feature Tests | HIGH |
| `http-refresh-database` | HTTP & Feature Tests | HIGH |
| `factory-define` | Model Factories | CRITICAL |
| `factory-states` | Model Factories | HIGH |
| `factory-sequences` | Model Factories | HIGH |
| `factory-relationships` | Model Factories | HIGH |
| `db-assert-has` | Database Assertions | HIGH |
| `db-assert-missing` | Database Assertions | HIGH |
| `db-assert-soft-deletes` | Database Assertions | MEDIUM |
| `fake-mail` | Faking Services | HIGH |
| `fake-queue` | Faking Services | HIGH |
| `fake-notification` | Faking Services | HIGH |
| `fake-event` | Faking Services | HIGH |
| `fake-storage` | Faking Services | HIGH |
| `fake-ai-agent` | Faking Services | HIGH |
| `fake-ai-media` | Faking Services | HIGH |
| `fake-ai-data` | Faking Services | HIGH |
| `auth-acting-as` | Authentication Testing | HIGH |
| `auth-sanctum` | Authentication Testing | HIGH |
| `pest-describe-it` | Test Organisation Patterns | MEDIUM |
| `pest-datasets` | Test Organisation Patterns | MEDIUM |
| `pest-hooks` | Test Organisation Patterns | MEDIUM |
