---
title: [Rule Title]
category: [commit|branch|pull-request|history|collaboration]
priority: [critical|high|medium]
tags: [tag1, tag2, tag3, tag4]
related: [related-rule-1, related-rule-2, related-rule-3]
---

# [Rule Title]

[One-sentence description of what this rule is about and why it matters]

## Bad Example

```bash
# [Description of the bad practice]
git command that demonstrates the problem
# Comments explaining why this is problematic

# [Another example of the bad practice]
git command that shows another anti-pattern
# More context about the issue
```

## Good Example

```bash
# [Description of the good practice]
git command that demonstrates the solution
# Comments explaining why this works well

# [Another example of the good practice]
git command that shows best practice
# More context about the benefits

# [Advanced or comprehensive example]
git command sequence for complete workflow
# Detailed explanation of the approach
```

## Why

[Explanation of why this rule matters, with specific benefits]

1. **[Benefit 1]**: Description of first major benefit
2. **[Benefit 2]**: Description of second major benefit
3. **[Benefit 3]**: Description of third major benefit
4. **[Benefit 4]**: Description of fourth major benefit
5. **[Benefit 5]**: Description of fifth major benefit

[Additional context about the rule:]

| Aspect | Detail |
|--------|--------|
| When to use | [Situations where this rule applies] |
| When NOT to use | [Exceptions or special cases] |
| Team size | [How this scales with team size] |
| Project type | [Project types this applies to] |

[Practical guidelines or checklist:]
- [Guideline 1]
- [Guideline 2]
- [Guideline 3]
- [Guideline 4]

[Optional: Configuration or automation:]
```bash
# Tool configuration
# Example: .gitconfig, GitHub settings, CI configuration
```

[Optional: Related commands or workflow:]
```bash
# Useful related commands
git command --options
# Explanation

# Common troubleshooting
git command to fix issues
# When to use this
```

---

## Template Guidelines

### YAML Frontmatter
- **title**: Clear, concise rule name
- **category**: One of: commit, branch, pull-request, history, collaboration
- **priority**: critical (always enforce), high (most projects), medium (context-dependent)
- **tags**: 3-5 descriptive tags for searchability
- **related**: 2-4 related rules that connect to this one

### Bad Example Section
- Show 2-3 concrete anti-patterns
- Use real git commands
- Add inline comments explaining the problem
- Be specific about why it's bad

### Good Example Section
- Show 2-3 correct approaches
- Use real git commands that work
- Add inline comments explaining benefits
- Progress from simple to comprehensive examples

### Why Section
- List 4-5 concrete benefits
- Use bold headers for each benefit
- Include a comparison table if helpful
- Add practical guidelines as bullet points
- Show configuration/automation when relevant

### General Writing Guidelines
- Use imperative mood for commands ("Do this", not "You should do this")
- Be specific and actionable
- Include real-world context
- Show both the problem and solution
- Focus on git best practices, commit conventions, PR workflows
- Use good git examples from well-known projects
