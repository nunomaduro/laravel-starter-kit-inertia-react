# Git Workflow Rule Sections

This document defines the organizational structure for git workflow rules.

## Section Categories

### 1. Commit Messages (commit)
**Priority:** Critical
**Description:** Standards for writing clear, meaningful commit messages that document code changes effectively.

Commit messages are the permanent record of why changes were made. Well-written commits enable:
- Automated changelog generation
- Semantic versioning
- Easier debugging with git bisect
- Code archaeology and understanding historical decisions
- Better code reviews

**Rules in this section:**
- Conventional commit format
- Atomic commits
- Imperative mood
- Meaningful subject lines
- Body for context
- Issue references
- Breaking change documentation

---

### 2. Branching Strategy (branch)
**Priority:** High
**Description:** Guidelines for creating, naming, and managing branches to enable parallel development and stable releases.

Effective branching strategies allow teams to work in parallel without conflicts while maintaining a stable main branch. This includes:
- Feature branch workflows
- Branch naming conventions
- Branch lifecycle management
- Release strategies

**Rules in this section:**
- Branch naming conventions
- Feature branch workflow
- Protected main branch
- Short-lived branches
- Delete merged branches
- Release branch strategy

---

### 3. Pull Requests (pull-request)
**Priority:** High
**Description:** Best practices for creating and reviewing pull requests to ensure code quality and knowledge sharing.

Pull requests are the primary mechanism for code review and collaboration. Good PR practices:
- Enable thorough code review
- Facilitate knowledge sharing
- Maintain code quality standards
- Document changes for future reference

**Rules in this section:**
- Small, focused PRs
- PR description templates
- Reviewer assignment
- CI checks
- Squash merge strategy
- Draft PR usage

---

### 4. History Management (history)
**Priority:** Medium
**Description:** Techniques for maintaining a clean, useful git history that aids debugging and understanding.

Git history is a valuable resource when:
- Debugging issues with git bisect
- Understanding why code exists
- Reverting problematic changes
- Onboarding new team members

**Rules in this section:**
- Rebase vs merge
- Avoiding force push on shared branches
- Cleaning up commits
- Tags and releases

---

### 5. Collaboration (collaboration)
**Priority:** Medium
**Description:** Practices for effective team collaboration using git workflows and communication.

Successful git collaboration requires:
- Effective code review practices
- Clear communication
- Conflict resolution skills
- Team coordination

**Rules in this section:**
- Code review best practices
- Merge conflict resolution
- Team communication

---

## Priority Levels

| Priority | When to Apply | Impact |
|----------|---------------|--------|
| **Critical** | Always enforce | Core to git workflow success, affects entire team |
| **High** | Enforce on most projects | Significant quality and collaboration impact |
| **Medium** | Context-dependent | Important but may vary by team/project |

## Category Relationships

```
Commit Messages (critical)
    ↓ forms foundation for
Pull Requests (high)
    ↓ reviewed through
Collaboration (medium)
    ↓ coordinates
Branching Strategy (high)
    ↓ managed via
History Management (medium)
```

## Using These Sections

When reviewing git practices:
1. Start with **Commit Messages** - the foundation
2. Check **Branching Strategy** - the structure
3. Review **Pull Requests** - the process
4. Verify **History Management** - the maintenance
5. Assess **Collaboration** - the team dynamics

Each section builds on the previous ones to create a comprehensive git workflow.
