---
title: Introduction
order: 1
---

# OI Laravel Seeds

OI Laravel Seeds is a Laravel package that enables you to export and import database seeders to and from JSON files with automatic dependency management.

## The Problem

Managing seed data in traditional Laravel seeders can be challenging:

- Seed data is written in PHP code, making it hard to version separately from logic
- Complex seeder dependencies require manual ordering
- There's no easy way to export current database state as seed data
- Updating seed data requires modifying PHP code and remembering the correct structure

## The Solution

OI Laravel Seeds solves these problems by allowing you to:

- **Export** database records to JSON files using a simple command
- **Import** seed data from JSON with automatic upserting (update or create)
- **Manage dependencies** automatically with topological sorting
- **Version seed data** alongside your code without touching PHP seeder logic
- **Filter sensitive data** from exports using customizable attribute filtering

## Key Features

### ExportableSeeder Trait

Add the `ExportableSeeder` trait to any Laravel Seeder class. The trait handles all export/import logic automatically.

### Topological Dependency Sorting

Define seeder dependencies, and the package automatically orders them correctly using depth-first topological sort. Circular dependencies are detected and reported.

### Upsert Logic

Data is imported using Laravel's `updateOrCreate()` method. Specify which columns are unique identifiers, and all other columns are updated on match.

### Exportable Attribute Filtering

Override `getExportableAttributes()` to remove sensitive fields like passwords, API keys, or tokens from exports.

### Multiple Models Per Seeder

A single seeder can handle multiple models with separate JSON files for each.

## Requirements

- **PHP**: 8.2 or higher
- **Laravel**: 11.0 or higher

## What's Next?

- [Installation](./installation.md) — Get up and running in minutes
- [Configuration](../configuration/configuration.md) — Configure storage location and defaults
- [The ExportableSeeder Trait](../usage/_index.md) — Learn the core API
- [Commands](../commands/make-seeder.md) — Master the Artisan commands
