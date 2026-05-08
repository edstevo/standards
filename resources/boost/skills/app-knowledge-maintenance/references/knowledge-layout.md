# Knowledge Layout

Load this when app docs or app skills need adding, splitting, pruning, or reorganizing.

## Docs

- Use `docs/README.md` only as a short index.
- Put durable source docs in focused directories, for example:
  - `docs/architecture/`
  - `docs/domains/{domain}/`
  - `docs/processes/{workflow}.md`
  - `docs/integrations/{provider}.md`
  - `docs/llm/{contract}.md`
- Add nested `README.md` files only as maps for that directory.
- Split a doc when a section becomes detail that only some tasks need.

## Skills

- Create `.ai/skills/{name}/SKILL.md` only when agents need an execution playbook.
- Prefer skills by coherent domain, workflow, integration, or convention.
- Do not create one arbitrary domain skill while similar domains have no coverage.
- If one domain is exceptional, say why in the skill's triggers or update notes.
- A skill should point to exact source docs, not broad indexes like `docs/README.md`, unless the task is docs navigation.

## Boundaries

- Guidelines say what every agent must always know.
- Docs say what is true about the app.
- Skills say how to work in a specific area.
- References hold conditional examples, schemas, checklists, scripts, and deep edge cases.

Do not copy the same explanation into multiple layers. Keep the shortest useful version in the highest layer, then link downward.
