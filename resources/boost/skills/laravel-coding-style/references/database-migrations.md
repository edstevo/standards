# Database Migrations

## Name Indexes and Constraints Safely

Do not rely on Laravel's generated identifier for a composite index or when the table and column names are long. Laravel derives names such as `<table>_<column>_<column>_index`; these can exceed the deployed database's identifier limit. MySQL permits at most 64 characters for identifiers such as index and constraint names.

- Give every composite index an explicit, concise, stable name.
- Explicitly name unique constraints and foreign keys when Laravel's inferred name could be long.
- Keep names within the strictest limit of the supported database engines; use 64 characters as the maximum for MySQL-backed applications.
- Use a short domain abbreviation when necessary, followed by a clear purpose and suffix such as `_idx`, `_uniq`, or `_fk`.
- Use the exact explicit name in `down()`; do not reconstruct or rely on an inferred name.

For example, avoid the generated `accounting_sync_records_status_failure_category_next_retry_at_index` name:

```php
public function up(): void
{
    Schema::table('accounting_sync_records', function (Blueprint $table): void {
        $table->index(
            ['status', 'failure_category', 'next_retry_at'],
            'asr_retry_policy_idx',
        );
    });
}

public function down(): void
{
    Schema::table('accounting_sync_records', function (Blueprint $table): void {
        $table->dropIndex('asr_retry_policy_idx');
    });
}
```

## Verification

- Count the final identifier, not just the table or column names.
- Inspect all indexes and constraints added by the migration, including those created through fluent shortcuts.
- Run the migration and rollback against the same database engine used in production when compatibility is material; SQLite tests do not expose MySQL's identifier-length limit.
