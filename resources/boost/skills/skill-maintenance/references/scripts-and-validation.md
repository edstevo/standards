# Scripts And Validation

Load this when a skill needs multi-step workflow checklists, validation loops, or reusable scripts.

## Multi-step Workflow Checklists

Use a checklist when order, dependencies, or validation gates matter.

```md
## Workflow Checklist

- [ ] Step 1: Inspect the source of truth.
- [ ] Step 2: Update the implementation.
- [ ] Step 3: Update related tests or fixtures.
- [ ] Step 4: Run the validation command.
- [ ] Step 5: Update docs, skills, references, or docblocks if durable knowledge changed.
```

Checklist items must be actionable and verifiable. Avoid vague items such as "clean up" or "finish remaining work".

## Validation Loops

For fragile workflows, tell the agent how to validate and iterate:

1. Make the change.
2. Run the listed validation command, script, checklist, or targeted test.
3. If validation fails, read the failure, fix the issue, and run validation again.
4. Continue until validation passes or the blocker is explicit.

For risky or batch operations, prefer plan-validate-execute:

- create a plan or mapping
- validate it against the source of truth
- only then apply the change

## Scripts

Use scripts when agents would otherwise reinvent repeated parsing, validation, transformation, inspection, or generation logic.

Script rules:

- Place scripts under the skill's `scripts/` directory.
- Reference scripts with paths relative to the skill root.
- Make scripts non-interactive.
- Provide useful `--help`.
- Emit clear errors that explain what was expected and what was received.
- Prefer structured output such as JSON, CSV, or TSV.
- Keep diagnostics on stderr and structured data on stdout.
- Make scripts idempotent where possible.
- Add `--dry-run` or explicit confirmation flags for destructive operations.
