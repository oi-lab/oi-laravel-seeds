---
title: Advanced
description: Dependencies, multiple models, relations, and bundled AI skills
order: 1
---

# Advanced

Beyond the basics, OI Laravel Seeds supports complex seeding scenarios:
ordering seeders by dependency, handling several models from one seeder,
exporting related records, and installing the bundled AI assistant skills.

## In This Section

### Seeder Dependencies

Declare which seeders must run first with `$dependencies`. The package resolves
the correct order using a topological sort and detects circular dependencies.

See [Seeder Dependencies](./dependencies.md).

### Multiple Models

A single seeder can export and import several models by pairing arrays of
`$jsonFilename` and `$modelClass` by index.

See [Multiple Models](./multiple-models.md).

### Exporting Relations

Include related records in exports with `$exportRelations` and the
`--with-relations` flag.

See [Exporting Relations](./export-relations.md).

### AI Assistant Skills

Install the bundled Claude Code and JetBrains Junie skills so your AI assistant
understands the package conventions.

See [AI Assistant Skills](./skills.md).

## Next Steps

- [Seeder Dependencies](./dependencies.md) — Order seeders automatically
- [Multiple Models](./multiple-models.md) — One seeder, several models
- [Exporting Relations](./export-relations.md) — Include related records
- [AI Assistant Skills](./skills.md) — Set up editor/assistant integration
