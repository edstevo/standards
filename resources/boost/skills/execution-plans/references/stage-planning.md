# Stage Planning Review

Read this reference only for a stage planning review or scope-expansion decision. A dispatch-time freshness check for an already approved stage follows `freshness-gate.md` and does not occupy the planning-task slot.

## Task Boundary

Use one dedicated `gpt-5.6-sol` `xhigh` task named `PLAN-1234 Stage 01 Planning`. Only one planning task may be active across the plan.

The task may inspect the repository and edit its existing current stage document only. It must not edit the master plan, another stage, source, tests, configuration, branches, or PRs; commit, push, implement, dispatch, or merge. Return every proposed change elsewhere as a controller suggestion.

The planning task owns dependency discovery and cross-stage compatibility for its stage. It must verify that one implementation agent can implement, verify, and hand off the outcome in one focused session and one understandable child PR. If not, formulate a stage split with the user and return it to the controller.

## Starting Context

Receive the master plan's concise stage map, current stage document, direct dependency documents, relevant contracts and decisions, project guidance, controller task ID, and repository access. Do not request every completed stage.

Build a private decision register separating:

- inherited and settled decisions;
- verified facts;
- genuinely open choices;
- risks and failure cases;
- dependencies and invalidation conditions;
- E2E effects; and
- exclusions and outside-stage suggestions.

## Limit Investigation To Planning Needs

Start from the controller's validated context package. Inspect only enough repository evidence to resolve a material user decision, confirm the single-component boundary, name the implementation seams, and design observable acceptance.

Do not run routine test suites, prove implementation correctness before implementation exists, repeat a closed dependency's code review, audit unrelated or future-stage areas, or search broadly after the required plan fields are supported. Existing handoffs and commit-scoped evidence are sufficient while their baselines and relevant paths remain valid.

Stop discovery when every material planning decision is settled and the stage document contains enough context for implementation and review. Record a non-material technical uncertainty as an implementation check instead of extending planning. Block only when the uncertainty can change scope, user behaviour, dependency compatibility, safety, or readiness.

## Guided User Review

1. Start with a short plain-English orientation: proposed outcome, why it matters, current position, important boundaries, and one realistic example.
2. Ask exactly one genuine decision question per turn. Explain consequences, tradeoffs, and an example before asking.
3. Answer clarification first, then repeat only the pending question. Preserve all other settled answers.
4. Add new issues to the private register without losing the current question. Reopen a decision only when contradictory evidence invalidates its basis.
5. After all questions are settled, present one complete plain-English decision brief covering outcome, behaviour, scope, exclusions, examples, failure cases, dependencies, risks, E2E effects, and readiness consequences.
6. Ask for explicit approval only after the complete brief.
7. After approval, update the stage document and send a separate technical handoff to the controller.

Never use the technical handoff as the user-facing review. Explain behaviour before mechanics and do not require the user to interpret paths, class names, schemas, hashes, commands, or handoff metadata unless they are themselves material decisions.

## Planning Checks

Before recommending readiness, confirm:

- the outcome and boundaries are explicit;
- the stage is one small isolated component;
- direct dependencies and inherited contracts are known;
- every affected complete journey was identified;
- relevant existing E2E scenarios were identified and inspected only as far as needed to judge planned coverage;
- required E2E additions or adjustments and their owner are in scope;
- observable acceptance, exact validation, environment assumptions, and safe recovery are defined;
- no material question remains; and
- invalidation and reapproval conditions are precise.

## Readiness Decision

- `not ready`: a decision, split, prerequisite, contract conflict, or authorization remains. Automatic promotion is `No`.
- `provisionally ready`: the plan and approval are complete; only one named upstream result or scheduling freshness check remains. Record exact assumptions, relevant paths, `Automatic promotion: Yes`, and the precise condition that would require user reapproval.
- `ready`: prerequisites and compatibility passed, the small-stage and E2E gates passed, and implementation is authorized.

The controller treats this as a recommendation until it accepts the document delta and handoff.

## Scope Expansion

When implementation or review reports broader required work, resume this task. Show the blocked outcome, evidence, proposed movement, effect if declined, and completed work. Conduct the guided review only for new or invalidated decisions, then update the reviewed plan after approval. Do not let implementation or review authorize expansion.

## Controller Handoff

The stage document is the complete technical record. The message to a controller that can access it contains only:

```text
Plan and stage: <ID and title>
Stage document: <path>
Changed sections: <concise list>
Gate result: <passed | blocked>
Implementation readiness: <state>
Remaining gate: <exact gate | none>
Automatic promotion: <yes | no>
User reapproval: <condition | not required>
Guided review: <complete | incomplete>
Final decision brief: <approved | pending>
Outside-stage suggestions: <none | concise list>
Recommended controller action: <next step>
```

Send this explicitly to the supplied controller task and confirm receipt before reporting completion. If delivery fails, keep the handoff available and report the exact blocker. Do not repeat the reviewed plan in the message unless the controller cannot read the document.
