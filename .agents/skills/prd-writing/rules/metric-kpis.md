---
title: Define 3-5 Key Performance Indicators
impact: HIGH
impactDescription: "Focuses the team on what matters most"
tags: metrics, kpis, performance-indicators, focus
---

## Define 3-5 Key Performance Indicators

**Impact: HIGH (Focuses the team on what matters most)**

Identify 3-5 KPIs that directly measure whether the feature solves the stated problem. More than 5 KPIs dilute focus. Fewer than 3 may miss important dimensions. Each KPI should tie back to the problem statement.

## Incorrect

```markdown
<!-- Bad: too many, unfocused, or no KPIs -->
## Metrics
- Number of users
- Page views
- Server uptime
- Database size
- API response time
- Number of short URLs created
- Number of clicks
- Bounce rate
- Session duration
- Error count
- CPU usage
- Memory usage
```

**Problems:**
- 12 metrics — team doesn't know which ones matter
- Mix of vanity metrics (page views) and infrastructure metrics (CPU)
- No connection to the problem being solved
- No targets — just things to measure
- Monitoring everything means prioritizing nothing

## Correct

```markdown
<!-- Good: 3-5 KPIs tied to the problem -->
## Key Performance Indicators

**Problem:** Marketing team has no way to track campaign link performance,
resulting in blind budget allocation across channels.

| # | KPI | Baseline | Target | Why This Matters |
|---|-----|----------|--------|-----------------|
| 1 | **Short URLs created per week** | 0 | 50+ | Measures adoption — are users actually using it? |
| 2 | **Click-through rate improvement** | 2.3% (long URLs) | 3.5%+ | Core value prop — do short URLs perform better? |
| 3 | **Analytics page views per user per week** | 0 | 3+ | Engagement — are users checking their data? |
| 4 | **Time to create a short URL** | N/A | < 30 seconds | Usability — is the workflow efficient? |
| 5 | **Redirect success rate** | N/A | > 99.9% | Reliability — does the core function work? |

### What We're NOT Tracking as KPIs
- Server CPU/memory: Infrastructure concern, not product success
- Total page views: Vanity metric, doesn't indicate value
- Database size: Ops metric, not relevant to user outcomes
```

**Benefits:**
- Team knows exactly which 5 numbers to watch
- Each KPI connects back to the problem statement
- "What we're NOT tracking" prevents metric creep
- Baselines enable before/after comparison

## Why

1. **Focus**: 5 KPIs are manageable; 15 are noise
2. **Alignment**: Everyone optimizes for the same outcomes
3. **Decision-making**: "Will this change improve KPI #2?" guides trade-offs
4. **Accountability**: Post-launch review checks specific numbers, not feelings

Reference: [Amplitude - North Star Metric](https://amplitude.com/blog/north-star-metric)
