---
title: Write a Concise Executive Summary
impact: CRITICAL
impactDescription: "First thing stakeholders read — sets the tone"
tags: structure, executive-summary, overview, communication
---

## Write a Concise Executive Summary

**Impact: CRITICAL (First thing stakeholders read — sets the tone)**

The executive summary is a single paragraph (3-5 sentences) that captures the problem, proposed solution, and expected impact. A busy stakeholder should understand the entire PRD from this section alone.

## Incorrect

```markdown
<!-- Bad: too long, too vague, solution-focused -->
## Executive Summary

We are going to build a URL shortening service. This will involve
creating a new database table, a controller, views for CRUD operations,
and an analytics dashboard. We'll use Laravel for the backend and
React for the frontend. The project will take approximately 4 weeks.
We need this because competitors have it. The service will support
custom slugs, expiry dates, SEO metadata, and click tracking with
geolocation. We'll also need admin functionality for user management.
Redis might be useful for caching. We should consider rate limiting too.
```

**Problems:**
- Reads like a feature list, not a summary
- No problem statement
- No measurable impact
- Implementation details don't belong here
- Too long — loses the reader

## Correct

```markdown
<!-- Good: problem → solution → impact in one paragraph -->
## Executive Summary

Marketing teams currently share long, unbranded URLs that reduce
click-through rates by 23% compared to short, branded links (source:
Q4 analytics). We propose a self-hosted URL shortening service that
allows authenticated users to create trackable short links with
custom slugs, expiry dates, and SEO metadata. Success is measured by
achieving >90% adoption among marketing team members within 30 days
and a 15% improvement in campaign click-through rates.
```

**Benefits:**
- Problem is clear in the first sentence
- Solution is specific but not prescriptive
- Impact is measurable and time-bound
- Entire PRD context in 4 sentences

## Why

1. **Respects reader time**: Executives and stakeholders skim — give them the essence upfront
2. **Forces clarity**: If you can't summarize it in a paragraph, you don't understand it well enough
3. **Enables quick decisions**: Approvers can say yes/no based on the summary alone
4. **Sets evaluation criteria**: The impact statement becomes the benchmark for success
