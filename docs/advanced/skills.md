---
title: AI Assistant Skills
description: Installing the bundled Claude Code and JetBrains Junie skills
section: advanced
order: 4
---

# AI Assistant Skills

The package ships an AI context skill describing how to use it. The recommended way to install it is the
unified `oi:skills` command (provided by `oi-lab/oi-laravel-development`), which discovers the skills declared
by every installed `oi-lab/*` package and lets you install the ones you want through an interactive picker with
a project (`.claude` + `.junie`) vs global (`~/.claude`) scope choice:

```bash
php artisan oi:skills
```

To install only this package's skill non-interactively:

```bash
php artisan oi:skills oilab-laravel-seeds --project
# or, for the global scope (~/.claude)
php artisan oi:skills oilab-laravel-seeds --global
```

Installing this skill:

- writes `SKILL.md` to `.claude/skills/oilab-laravel-seeds/` (Claude Code) and
  `.junie/skills/oilab-laravel-seeds/` (JetBrains Junie);
- adds (or refreshes) an `=== oi-lab/oi-laravel-seeds rules ===` section in your project's `CLAUDE.md`.

> A package-local command `php artisan oi-seeds:install-ai-skill` is still available for projects that don't use
> `oi-lab/oi-laravel-development`, but it is **deprecated** in favor of `oi:skills`.

The canonical source is `resources/stubs/ai-skill.md`. After changing the package, maintainers re-sync the
committed skill copies with:

```bash
composer sync-ai-skills
```

This also runs automatically on `post-autoload-dump`.
