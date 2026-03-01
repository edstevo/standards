---
name: eloquent-relationship-traits
description: Use reusable Eloquent relationship traits (plus optional model initializers) to keep models slim, consistent, and composable.
---

# Eloquent Relationship Traits

This codebase keeps Eloquent models lean by extracting relationships into small, reusable traits under `App\Models\Relationships`.

Use this skill whenever you:
- Add / modify Eloquent relationships
- Create new models that share common relationships
- Refactor bulky models into composable concerns
- Need consistent docblocks / typing around relationships

## Core conventions

### 1) One relationship per trait (small, composable)
Prefer single-purpose traits:
- `BelongsToShop`
- `HasManyFulfillmentLines`
- `MorphOneAiProfile`
- `MorphManyFulfillmentOrders`

Traits should be easy to reuse and easy to search for.

**Naming pattern**
- `BelongsToX`
- `HasManyX`
- `HasOneX`
- `MorphOneX`
- `MorphManyX`
- `BelongsToManyX` (if needed)

Keep the trait name aligned with the relationship type and related model(s).

### 2) Trait location and namespace
- Traits live in `app/Models/Relationships`
- Namespace: `App\Models\Relationships`

@verbatim
<code-snippet name="Trait namespace" lang="php">
namespace App\Models\Relationships;
</code-snippet>
@endverbatim

### 3) Relationship method names are the domain language
Use the most natural relationship method name for the domain:
- `shop()`
- `fulfillmentLines()`
- `aiProfile()`
- `destination()`

Avoid awkward method names just to match the trait name. The **trait name** can be explicit; the **relationship method** should read naturally.

### 4) Use model trait initializers when a relationship implies fillable columns
If a relationship requires a foreign key that should be mass-assignable, use the trait initializer hook:

- Method name must match `initialize{TraitClassBasename}()`
- Merge required columns into `$fillable` using `mergeFillable([...])`

@verbatim
<code-snippet name="BelongsTo with initializer merging fillable" lang="php">
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
</code-snippet>
@endverbatim

Guidelines:
- Only do this when the FK being fillable is intentional for the app.
- Prefer `mergeFillable` over redefining `$fillable` in multiple models.

### 5) Always add docblocks for relationship properties
Every trait should document:
- The FK column (when applicable)
- The related model property
- Collections for `HasMany` / `MorphMany` etc.

@verbatim
<code-snippet name="HasMany docblock example" lang="php">
/**
 * @property \Illuminate\Database\Eloquent\Collection<int, FulfillmentLine> $fulfillmentLines
 */
</code-snippet>
@endverbatim

This improves IDE/static analysis support and makes relationships discoverable.

### 6) Use correct relation return types
Return the concrete relation type:
- `BelongsTo`
- `HasMany`
- `MorphOne`
- `MorphMany`
- etc.

@verbatim
<code-snippet name="HasMany relationship method with correct return type" lang="php">
public function fulfillmentLines(): HasMany
{
    return $this->hasMany(FulfillmentLine::class);
}
</code-snippet>
@endverbatim

### 7) Polymorphic relationships: keep morph names consistent
For morph relations:
- The morph name (`'fulfillable'`, `'profileable'`, etc.) must match database columns (`*_type`, `*_id`) and
stay consistent across the codebase.

@verbatim
<code-snippet name="MorphOne example" lang="php">
<?php

namespace App\Models\Relationships;

use App\Models\AiProfile;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
* @property AiProfile $aiProfile
*/
trait MorphOneAiProfile
{
    public function aiProfile(): MorphOne
    {
        return $this->morphOne(AiProfile::class, 'profileable');
    }
}
</code-snippet>
@endverbatim

If introducing a new morph, choose a name that is:
- Clear in the domain
- Stable long-term (renames are painful)

## File structure

When adding a new relationship trait:
- Create: `app/Models/Relationships/{TraitName}.php`
- Add relationship + optional initializer + docblock
- Import the trait into the model using `use {TraitName};`

@verbatim
<code-snippet name="Model using relationship traits" lang="php">
<?php

namespace App\Models;

use App\Models\Relationships\BelongsToSupplier;
use App\Models\Relationships\HasManyPurchaseOrderLines;
use App\Models\Relationships\MorphManyFulfillments;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
use BelongsToSupplier;
use HasManyPurchaseOrderLines;
use MorphManyFulfillments;

// ...
}
</code-snippet>
@endverbatim

## Implementation templates

### BelongsTo template
@verbatim
<code-snippet name="BelongsTo relationship trait template" lang="php">
<?php

namespace App\Models\Relationships;

use App\Models\RelatedModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
* @property int $related_model_id
* @property RelatedModel $relatedModel
*/
trait BelongsToRelatedModel
{
    public function relatedModel(): BelongsTo
    {
        return $this->belongsTo(RelatedModel::class);
    }

    public function initializeBelongsToRelatedModel(): void
    {
        $this->mergeFillable(['related_model_id']);
    }
}
</code-snippet>
@endverbatim

### HasMany template
@verbatim
<code-snippet name="HasMany relationship trait template" lang="php">
<?php

namespace App\Models\Relationships;

use App\Models\RelatedModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
* @property \Illuminate\Database\Eloquent\Collection<int, RelatedModel> $relatedModels
*/
trait HasManyRelatedModels
{
    public function relatedModels(): HasMany
    {
        return $this->hasMany(RelatedModel::class);
    }
}
</code-snippet>
@endverbatim

### MorphOne template
@verbatim
<code-snippet name="MorphOne relationship trait template" lang="php">
<?php

namespace App\Models\Relationships;

use App\Models\RelatedModel;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
* @property RelatedModel $relatedModel
*/
trait MorphOneRelatedModel
{
    public function relatedModel(): MorphOne
    {
        return $this->morphOne(RelatedModel::class, 'morphable');
    }
}
</code-snippet>
@endverbatim

### MorphMany template
@verbatim
<code-snippet name="MorphMany relationship trait template" lang="php">
<?php

namespace App\Models\Relationships;

use App\Models\RelatedModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
* @property Collection<int, RelatedModel> $relatedModels
*/
trait MorphManyRelatedModels
{
    public function relatedModels(): MorphMany
    {
        return $this->morphMany(RelatedModel::class, 'morphable');
    }
}

</code-snippet>
@endverbatim

## Do / Don’t

### Do
- Keep traits tiny and focused.
- Prefer predictable naming and placement.
- Add docblocks so relationships are discoverable and typed.
- Use `initializeTraitName()` to merge fillable FK columns when
appropriate.
- Keep morph names consistent (`fulfillable`, `profileable`,
etc).

### Don’t
- Dump multiple unrelated relationships into one big trait.
- Hide important relationship constraints where they’re hard to
notice.
- Add fillables in random places when a trait initializer would
keep it consistent.
- Rename morph names casually (treat as a breaking change).

## Quick checklist when adding a relationship
- [ ] Create a trait in `App\Models\Relationships`
- [ ] Add relationship method with correct return type
- [ ] Add docblock properties (FK + model / collection)
- [ ] Add initializer if FK should be fillable
- [ ] Import trait into model
- [ ] Ensure morph name matches DB columns and existing
conventions
