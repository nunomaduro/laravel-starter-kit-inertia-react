---
title: .gitignore Best Practices
category: collaboration
priority: medium
tags: [gitignore, configuration, collaboration, security]
related: [collab-communication, branch-main-protected]
---

# .gitignore Best Practices

Use `.gitignore` to keep secrets, build artifacts, and local config out of the repository.

## Bad Example

```bash
# No .gitignore — committing everything
git add .
git commit -m "initial commit"
# Commits: node_modules/, .env, dist/, .DS_Store, *.log

# Overly broad ignore (ignores too much)
echo "*" > .gitignore
# Breaks git — nothing gets tracked

# .gitignore added after secrets already committed
git log --all --full-history -- .env
# commit abc123: "add .env"  ← secret is in history forever
# Adding to .gitignore now does NOT remove it from history

# Personal ignores in the shared .gitignore
echo ".idea/" >> .gitignore
echo "*.suo" >> .gitignore
# IDE-specific files belong in global gitignore, not the repo
```

## Good Example

```gitignore
# .gitignore — committed to the repository

# Dependencies
node_modules/
vendor/
.venv/
__pycache__/

# Build outputs
dist/
build/
.next/
out/
*.egg-info/

# Environment & secrets — NEVER commit these
.env
.env.local
.env.*.local
*.pem
*.key
secrets.json

# Logs
*.log
logs/
npm-debug.log*

# Test coverage
coverage/
.nyc_output/

# OS files — use global gitignore instead, but common to include
.DS_Store
Thumbs.db

# Cache
.cache/
.parcel-cache/
.eslintcache
```

```bash
# Set up a global gitignore for personal IDE/OS files
git config --global core.excludesfile ~/.gitignore_global

# ~/.gitignore_global
.idea/
.vscode/
*.suo
*.swp
.DS_Store
Thumbs.db
```

```bash
# If secrets were accidentally committed — remove from history
git filter-repo --path .env --invert-paths
# Rotate ALL exposed secrets immediately — history removal is not enough
# Force push and notify all collaborators to re-clone
```

## Why

A well-maintained `.gitignore` prevents three categories of problems:

1. **Security**: `.env` files, private keys, and API tokens must never reach the repository. Once committed, secrets are compromised even after removal — they remain in git history and may have been cloned or cached.

2. **Repository bloat**: `node_modules/` can contain 100,000+ files. Committing it makes clones slow and diffs unreadable.

3. **Noise**: Generated files (`dist/`, `*.log`, OS artifacts) create meaningless diffs and conflict with teammates' local builds.

**Key rules:**
- Add `.gitignore` as the very first commit
- Never commit `.env` — use `.env.example` with placeholder values instead
- IDE-specific files (`.idea/`, `.vscode/`) go in the global `~/.gitignore_global`, not the repo
- Use `git rm --cached <file>` to stop tracking a file that was accidentally committed (then add to `.gitignore`)
- After committing secrets: rotate credentials immediately, then clean history
