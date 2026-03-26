---
title: Start with the Problem, Not the Solution
impact: CRITICAL
impactDescription: "Foundation of effective product thinking"
tags: discovery, problem-statement, product-thinking
---

## Start with the Problem, Not the Solution

**Impact: CRITICAL (Foundation of effective product thinking)**

Every PRD must begin by articulating the problem clearly before proposing any solution. Solution-first PRDs lead to building features nobody needs or solving the wrong problem entirely.

## Incorrect

```markdown
<!-- Bad: jumping straight to a solution -->
# PRD: Add Redis Caching

## Overview
We need to implement Redis caching across all API endpoints
with a 5-minute TTL to improve performance.

## Requirements
- Install Redis 7.x
- Cache all GET responses
- Add cache invalidation on write operations
- Configure TTL to 300 seconds
```

**Problems:**
- No explanation of what problem caching solves
- No evidence that caching is the right solution
- No measurement of current performance
- Prescribes implementation before understanding the need
- Impossible to evaluate success without knowing the starting point

## Correct

```markdown
<!-- Good: problem-first approach -->
# PRD: Dashboard Performance Optimization

## Problem
Dashboard page load time averages 4.2s (p95). Users abandon
after 3s, resulting in 35% bounce rate on the dashboard page.
Support tickets about "slow dashboard" increased 3x last quarter.

**Root Cause:** 80% of latency comes from repeated identical
database queries across concurrent users viewing the same data.

## Proposed Solution
Introduce a query-level caching layer for dashboard endpoints.

**Target:**
- Reduce p95 load time from 4.2s to < 1.5s
- Reduce dashboard bounce rate from 35% to < 15%
- Reduce database load by 60% for dashboard queries

## Why Now
- User complaints trending upward (12 tickets last week)
- Database CPU at 78% during peak hours
- New enterprise client onboarding next month with 500 users
```

**Benefits:**
- Clear problem enables the team to evaluate whether the solution fits
- Measurable current state provides a baseline for success
- "Why now" creates urgency and justifies prioritization
- Engineering can propose alternative solutions if caching isn't optimal

## Why

1. **Prevents wasted effort**: Building the wrong thing is more expensive than building nothing
2. **Enables better solutions**: When the problem is clear, the team can brainstorm alternatives
3. **Aligns stakeholders**: Everyone agrees on *what* to solve before debating *how*
4. **Provides evaluation criteria**: You can measure whether the solution actually solved the problem

Reference: [Silicon Valley Product Group - Product Discovery](https://www.svpg.com/product-discovery/)
