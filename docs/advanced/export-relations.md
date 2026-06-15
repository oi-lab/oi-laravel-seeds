---
title: Exporting Relations
order: 3
---

# Exporting Relations

The `$exportRelations` property allows you to include related data when exporting. Relations are nested as arrays in the JSON output.

## Configuration

Define which relations to export using the `$exportRelations` property:

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use OiLab\OiLaravelSeeds\Traits\ExportableSeeder;

class UserSeeder extends Seeder
{
    use ExportableSeeder;

    protected string $jsonFilename = 'users.json';

    protected string $modelClass = User::class;

    protected array $exportRelations = ['posts', 'comments'];

    public function run(): void
    {
        $this->importData();
    }
}
```

The relation names must match methods on your Eloquent model.

## Exporting with Relations

Export data including relations using the `--with-relations` flag:

```bash
php artisan seed:export --seeder=UserSeeder --with-relations
```

Without this flag, relations are not exported:

```bash
php artisan seed:export --seeder=UserSeeder
```

## Example: User with Posts and Comments

### Model Relationships

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
```

### Seeder Configuration

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use OiLab\OiLaravelSeeds\Traits\ExportableSeeder;

class UserSeeder extends Seeder
{
    use ExportableSeeder;

    protected string $jsonFilename = 'users.json';

    protected string $modelClass = User::class;

    protected array $exportRelations = ['posts', 'comments'];

    public function run(): void
    {
        $this->importData();
    }
}
```

### Exported JSON

When you run `php artisan seed:export --seeder=UserSeeder --with-relations`, the generated JSON includes relations:

```json
[
    {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "created_at": "2025-01-01T00:00:00.000000Z",
        "updated_at": "2025-01-01T00:00:00.000000Z",
        "posts": [
            {
                "id": 10,
                "user_id": 1,
                "title": "My First Post",
                "content": "Hello world",
                "published_at": "2025-01-01T00:00:00.000000Z",
                "created_at": "2025-01-01T00:00:00.000000Z",
                "updated_at": "2025-01-01T00:00:00.000000Z"
            },
            {
                "id": 11,
                "user_id": 1,
                "title": "Another Post",
                "content": "More content",
                "published_at": "2025-01-02T00:00:00.000000Z",
                "created_at": "2025-01-02T00:00:00.000000Z",
                "updated_at": "2025-01-02T00:00:00.000000Z"
            }
        ],
        "comments": [
            {
                "id": 100,
                "user_id": 1,
                "post_id": 10,
                "body": "Great post!",
                "created_at": "2025-01-01T12:00:00.000000Z",
                "updated_at": "2025-01-01T12:00:00.000000Z"
            },
            {
                "id": 101,
                "user_id": 1,
                "post_id": 11,
                "body": "I agree!",
                "created_at": "2025-01-02T12:00:00.000000Z",
                "updated_at": "2025-01-02T12:00:00.000000Z"
            }
        ]
    },
    {
        "id": 2,
        "name": "Jane Smith",
        "email": "jane@example.com",
        "created_at": "2025-01-01T00:00:00.000000Z",
        "updated_at": "2025-01-01T00:00:00.000000Z",
        "posts": [],
        "comments": []
    }
]
```

## Important: Relations Are Not Imported

When importing JSON with embedded relations, **only the parent model is imported**. Relations are included in the export for documentation and backup purposes, but are not automatically created during import.

Example:

```bash
php artisan seed:import --seeder=UserSeeder
```

This imports:
- ✅ Users from the JSON
- ❌ Posts from the JSON (NOT imported)
- ❌ Comments from the JSON (NOT imported)

### Importing Relations

To import related data, create separate seeders for each model with appropriate dependencies:

```php
// UserSeeder.php
protected string $jsonFilename = 'users.json';
protected string $modelClass = User::class;

// PostSeeder.php
protected string $jsonFilename = 'posts.json';
protected string $modelClass = Post::class;
protected array $dependencies = [UserSeeder::class];

// CommentSeeder.php
protected string $jsonFilename = 'comments.json';
protected string $modelClass = Comment::class;
protected array $dependencies = [UserSeeder::class, PostSeeder::class];
```

Then import all:

```bash
php artisan seed:import
```

## Nested Relations

You can export relations that have their own relations:

```php
protected array $exportRelations = ['posts.comments', 'posts.tags'];
```

Eloquent's `load()` method supports dot notation for nested relations.

Example model relationships:

```php
class User extends Model
{
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}

class Post extends Model
{
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }
}
```

Exported JSON includes nested relations:

```json
[
    {
        "id": 1,
        "name": "John Doe",
        "posts": [
            {
                "id": 10,
                "title": "My First Post",
                "comments": [
                    {
                        "id": 100,
                        "body": "Great post!"
                    }
                ],
                "tags": [
                    {
                        "id": 5,
                        "name": "php"
                    }
                ]
            }
        ]
    }
]
```

## Filtering Relation Attributes

Override `getExportableAttributes()` to customize which attributes are exported for relations:

```php
protected function getExportableAttributes(mixed $model): array
{
    // For Post instances (from relations)
    if ($model instanceof Post) {
        return [
            'id',
            'title',
            'content',
            'published_at',
            'created_at',
            'updated_at',
        ];
    }

    // For Comment instances (from relations)
    if ($model instanceof Comment) {
        return [
            'id',
            'body',
            'created_at',
            'updated_at',
        ];
    }

    // For User instances (parent)
    if ($model instanceof User) {
        return [
            'id',
            'name',
            'email',
            'created_at',
            'updated_at',
        ];
    }

    return $model->getAttributes();
}
```

This allows different attributes for each relation type.

## BelongsTo Relations

You can export BelongsTo relations too:

```php
class Post extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

If exporting from a Post seeder:

```php
protected array $exportRelations = ['user'];
```

The Post's related User is included in the export (avoiding redundancy if you already export users separately).

## Many-to-Many Relations

BelongsToMany relations are exported as nested arrays:

```php
class Post extends Model
{
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }
}
```

Exported JSON:

```json
{
    "id": 10,
    "title": "My Post",
    "tags": [
        {
            "id": 5,
            "name": "php"
        },
        {
            "id": 6,
            "name": "laravel"
        }
    ]
}
```

## Use Cases

### Backup and Documentation

Export relations to create a complete backup of your data structure:

```bash
php artisan seed:export --with-relations > backup.json
```

### Development Seed Data

Include relations in exports to understand data relationships when developing:

```bash
php artisan seed:export --seeder=OrderSeeder --with-relations
```

See the complete order structure with items, payments, and shipments.

### Testing Fixtures

Use exported JSON with relations as test fixtures.

## Best Practices

- **Keep relations focused** — Export only relations you need; deeply nested relations can create large JSON files
- **Use separate seeders for complex structures** — If relations have many fields, create dedicated seeders
- **Test imports** — Verify that your import seeders work correctly after exporting with relations
- **Document your schema** — Use exported relations to understand data structures

## Next Steps

- [Multiple Models](./multiple-models.md) — Import multiple models per seeder
- [Importing Data](../usage/importing.md) — Understand how imports work
- [seed:export Command](../commands/seed-export.md) — Full command reference
