# Eloquent Relationship Traits

Load this reference when adding or refactoring Eloquent relationships.

This codebase keeps models lean by extracting reusable relationships into small traits under `App\Models\Relationships`.

## Core Rules

- Prefer one relationship per trait.
- Keep traits small, composable, and searchable.
- Put traits in `app/Models/Relationships`.
- Use namespace `App\Models\Relationships`.
- Name traits after the relationship type and related model, such as `BelongsToShop`, `HasManyFulfillmentLines`, `MorphOneAiProfile`, or `BelongsToManyUsers`.
- Keep relationship method names natural to the domain, such as `shop()`, `fulfillmentLines()`, `aiProfile()`, or `destination()`.
- Return concrete Eloquent relation types.
- Add property docblocks for related models, collections, and FK columns.

## Fillable Foreign Keys

When a relationship implies a foreign key that should intentionally be mass assignable, merge it from the trait initializer.

```php
public function initializeBelongsToShop(): void
{
    $this->mergeFillable(['shop_id']);
}
```

Only do this when the FK being fillable is intentional. Prefer `mergeFillable()` in the trait over redefining `$fillable` in multiple models.

## BelongsTo Template

```php
<?php

namespace App\Models\Relationships;

use App\Models\Shop;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $shop_id
 * @property Shop $shop
 */
trait BelongsToShop
{
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function initializeBelongsToShop(): void
    {
        $this->mergeFillable(['shop_id']);
    }
}
```

## HasMany Template

```php
<?php

namespace App\Models\Relationships;

use App\Models\FulfillmentLine;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property Collection<int, FulfillmentLine> $fulfillmentLines
 */
trait HasManyFulfillmentLines
{
    public function fulfillmentLines(): HasMany
    {
        return $this->hasMany(FulfillmentLine::class);
    }
}
```

## Polymorphic Relationships

For morph relations, keep morph names stable and consistent with database columns.

Examples:
- `fulfillable` -> `fulfillable_type`, `fulfillable_id`
- `profileable` -> `profileable_type`, `profileable_id`

Treat morph-name changes as breaking changes.

```php
<?php

namespace App\Models\Relationships;

use App\Models\AiProfile;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @property AiProfile|null $aiProfile
 */
trait MorphOneAiProfile
{
    public function aiProfile(): MorphOne
    {
        return $this->morphOne(AiProfile::class, 'profileable');
    }
}
```

## Checklist

- [ ] Create `app/Models/Relationships/{TraitName}.php`.
- [ ] Add one relationship method with the correct concrete return type.
- [ ] Add docblock properties for FK, model, or collection.
- [ ] Add initializer when an FK should intentionally be fillable.
- [ ] Import the trait into the model.
- [ ] Check morph names against DB columns and existing conventions.
