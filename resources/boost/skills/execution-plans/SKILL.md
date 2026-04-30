---
name: execution-plans
description: Create, maintain, and implement self-contained ExecPlans for complex features and significant refactors. Use when a task needs a living design-and-delivery plan that guides the work from research through implementation and verification.
license: MIT
metadata:
  domain: workflow
  role: specialist
  scope: planning
  triggers: ExecPlan, execution plan, pull request plan, PR plan, complex feature, significant refactor, design doc, milestone, living plan
---

# Execution Plans

Use this skill when you:
- design a complex feature or significant refactor
- implement work that should be driven by a living plan
- revise, discuss, or execute an ExecPlan

Follow the guidance below literally. The plan must live in the GitHub pull request body and stay self-contained and current enough that a novice can restart from only the PR body and the working tree.

## Codex Execution Plans (ExecPlans)

This document describes the requirements for an execution plan ("ExecPlan"), a design document that a coding agent can follow to deliver a working feature or system change. Treat the reader as a complete beginner to this repository: they have only the current working tree and the ExecPlan in the GitHub pull request body. There is no memory of prior plans and no external context.

## How to use ExecPlans in PRs

Every ExecPlan must be written directly in the body of a GitHub pull request. Never create local Markdown plan files such as `PLANS.md`, `PLAN.md`, `.agent/PLANS.md`, `docs/plans/*.md`, or temporary local plan files. The PR body is the source of truth for the plan and progress.

When authoring an executable specification (ExecPlan), open or update the GitHub PR and put the plan in the PR body. Be thorough in reading (and re-reading) source material to produce an accurate specification. When creating a spec, start from the skeleton and flesh it out as you do your research.

When implementing an executable specification (ExecPlan), do not prompt the user for "next steps"; simply proceed to the next checklist item. Keep all sections up to date, add or split entries in the checklist at every stopping point to affirmatively state the progress made and next steps. Resolve ambiguities autonomously, and commit frequently.

When discussing an executable specification (ExecPlan), record decisions in a log in the PR body for posterity; it should be unambiguously clear why any change to the specification was made. ExecPlans are living documents, and it should always be possible to restart from only the PR body and no other work.

When researching a design with challenging requirements or significant unknowns, use milestones to implement proof of concepts, toy implementations, and similar validation steps that show whether the proposal is feasible. Read the source code of libraries by finding or acquiring them, research deeply, and include prototypes to guide a fuller implementation.

## Requirements

NON-NEGOTIABLE REQUIREMENTS:

* Every ExecPlan must be fully self-contained. Self-contained means that in its current form it contains all knowledge and instructions needed for a novice to succeed.
* Every ExecPlan is a living document. Contributors are required to revise it as progress is made, as discoveries occur, and as design decisions are finalized. Each revision must remain fully self-contained.
* Every ExecPlan must enable a complete novice to implement the feature end-to-end without prior knowledge of this repo.
* Every ExecPlan must produce a demonstrably working behavior, not merely code changes to "meet a definition".
* Every ExecPlan must define every term of art in plain language or do not use it.

Purpose and intent come first. Begin by explaining, in a few sentences, why the work matters from a user's perspective: what someone can do after this change that they could not do before, and how to see it working. Then guide the reader through the exact steps to achieve that outcome, including what to edit, what to run, and what they should observe.

The agent executing your plan can list files, read files, search, run the project, and run tests. It does not know any prior context and cannot infer what you meant from earlier milestones. Repeat any assumption you rely on. Do not point to external blogs or docs; if knowledge is required, embed it in the plan itself in your own words. If an ExecPlan builds upon a prior ExecPlan in another PR body, link that PR and summarize all relevant context directly in the current PR body.

## Formatting

Format and envelope are simple and strict. Each ExecPlan must be normal GitHub Markdown written directly in the PR body. Do not wrap the entire ExecPlan in a fenced code block. Use headings, paragraphs, and checklists normally. Fenced code blocks are allowed only for commands, transcripts, diffs, payload examples, or code excerpts inside a section.

Write in plain prose. Prefer sentences over lists. Avoid checklists, tables, and long enumerations unless brevity would obscure meaning. Checklists are permitted only in the `Progress` section, where they are mandatory and where the plan of work also lives. The finished `Progress` section should contain only checkbox items and, when useful, short subsection headings that group those items. Narrative sections must remain prose-first.

## Guidelines

Self-containment and plain language are paramount. If you introduce a phrase that is not ordinary English ("daemon", "middleware", "RPC gateway", "filter graph"), define it immediately and remind the reader how it manifests in this repository (for example, by naming the files or commands where it appears). Do not say "as defined previously" or "according to the architecture doc." Include the needed explanation here, even if you repeat yourself.

Avoid common failure modes. Do not rely on undefined jargon. Do not describe "the letter of a feature" so narrowly that the resulting code compiles but does nothing meaningful. Do not outsource key decisions to the reader. When ambiguity exists, resolve it in the plan itself and explain why you chose that path. Err on the side of over-explaining user-visible effects and under-specifying incidental implementation details.

Anchor the plan with observable outcomes. State what the user can do after implementation, the commands to run, and the outputs they should see. Acceptance should be phrased as behavior a human can verify ("after starting the server, navigating to [http://localhost:8080/health](http://localhost:8080/health) returns HTTP 200 with body OK") rather than internal attributes ("added a HealthCheck struct"). If a change is internal, explain how its impact can still be demonstrated (for example, by running tests that fail before and pass after, and by showing a scenario that uses the new behavior).

Specify repository context explicitly. Name files with full repository-relative paths, name functions and modules precisely, and describe where new files should be created. If touching multiple areas, include a short orientation paragraph that explains how those parts fit together so a novice can navigate confidently. When running commands, show the working directory and exact command line. When outcomes depend on environment, state the assumptions and provide alternatives when reasonable.

Validation is not optional. Include instructions to run tests, to start the system if applicable, and to observe it doing something useful. Describe comprehensive testing for any new features or capabilities. Include expected outputs and error messages so a novice can tell success from failure. Where possible, show how to prove that the change is effective beyond compilation (for example, through a small end-to-end scenario, a CLI invocation, or an HTTP request/response transcript). State the exact test commands appropriate to the project's toolchain and how to interpret their results.

Capture durable evidence in `Artifacts and Notes`. This section is for compact proof and handoff material that helps the next agent verify, resume, or understand the plan. Include only items that remain useful after the command has finished: short terminal transcripts, important test output, small diffs or file excerpts, generated file paths, sample payloads, screenshots or artifact paths, and brief notes explaining why those artifacts matter. Do not dump full logs, large patches, routine command output, or links to local-only files. Keep each entry concise and label what it proves.

## Milestones

Milestones are narrative, not bureaucracy. If you break the work into milestones, introduce each with a brief paragraph that describes the scope, what will exist at the end of the milestone that did not exist before, the commands to run, and the acceptance you expect to observe. Keep it readable as a story: goal, work, result, proof. The `Progress` checklist is the authoritative plan of work and progress tracker; milestones tell the broader story when the plan is large enough to need them. Never abbreviate a milestone merely for the sake of brevity, do not leave out details that could be crucial to a future implementation.

Each milestone must be independently verifiable and incrementally implement the overall goal of the execution plan.

## Living plans and design decisions

* ExecPlans are living documents. As you make key design decisions, update the plan to record both the decision and the thinking behind it. Record all decisions in the `Decision Log` section.
* ExecPlans must contain and maintain a `Purpose / Big Picture` section, a `Progress` section, a `Surprises & Discoveries` section, and a `Decision Log`. These are not optional.
* The `Purpose / Big Picture` section must combine the goal, user-visible outcome, repository context, orientation, and final retrospective. It should explain what someone gains, how to see it working, which files and concepts matter, and, once complete, what was achieved, what remains, and what was learned.
* The `Progress` section is the combined plan of work and progress tracker. Break it into manageable chunks that can be implemented and verified independently. If multiple logical groups are required, add short subsection headings under `Progress` and put a checklist under each one.
* Each `Progress` checklist item should name a concrete chunk of work, the relevant files or area when useful, and the verification signal for that chunk. Avoid vague items like "finish backend" or "fix tests"; prefer items like "Add the `InvoiceStatus` enum in `app/Definitions/InvoiceStatus.php` and verify the new enum test passes."
* When an item is completed, keep it checked and add a timestamp plus a short outcome if the result is not obvious. When an item is partially completed, split it into a checked item for the completed work and an unchecked item for the remaining work.
* When you discover optimizer behavior, performance tradeoffs, unexpected bugs, or inverse/unapply semantics that shaped your approach, capture those observations in the `Surprises & Discoveries` section with short evidence snippets (test output is ideal).
* If you change course mid-implementation, document why in the `Decision Log` and reflect the implications in `Progress`. Plans are guides for the next contributor as much as checklists for you.
* At completion of a major task or the full plan, update `Purpose / Big Picture` with the achieved outcome, remaining gaps, and lessons learned.

## Prototyping Milestones and Parallel Implementations

It is acceptable and often encouraged to include explicit prototyping milestones when they de-risk a larger change. Examples: adding a low-level operator to a dependency to validate feasibility, or exploring two composition orders while measuring optimizer effects. Keep prototypes additive and testable. Clearly label the scope as prototyping, describe how to run and observe results, and state the criteria for promoting or discarding the prototype.

Prefer additive code changes followed by subtractions that keep tests passing. Parallel implementations (for example, keeping an adapter alongside an older path during migration) are fine when they reduce risk or enable tests to continue passing during a large migration. Describe how to validate both paths and how to retire one safely with tests. When working with multiple new libraries or feature areas, consider creating spikes that evaluate the feasibility of these features independently of one another, proving that the external library performs as expected and implements the features we need in isolation.

## Skeleton of a Good ExecPlan

    # <Short, action-oriented description>

    This ExecPlan is a living document. The sections `Purpose / Big Picture`, `Progress`, `Surprises & Discoveries`, and `Decision Log` must be kept up to date as work proceeds.

    This ExecPlan lives in the GitHub PR body. Do not create a local `.md` plan file for this work.

    ## Purpose / Big Picture

    Explain what someone gains after this change and how they can see it working. State the user-visible behavior you will enable. Describe the current state relevant to this task as if the reader knows nothing. Name the key files and modules by full path. Define any non-obvious term you will use. Do not refer to prior plans. At completion, summarize what was achieved, what remains, and any lessons learned.

    ## Progress

    ### Discovery

    - [x] (2025-10-01 13:00Z) Read `app/Filament/App/Resources/Customers/CustomerResource.php` and existing customer tests; confirmed current list and view page structure.
    - [ ] Identify the exact user-visible behavior to add and record the expected verification in `Validation and Acceptance`.

    ### Implementation

    - [ ] Add the smallest behavior slice in `<path/to/file>` and verify it with `<targeted command or manual check>`.
    - [ ] Add or update focused tests in `<path/to/test>` for the new behavior.
    - [ ] Update related documentation, configuration, or interfaces touched by this change.

    ### Validation

    - [ ] Run the targeted test command: `<command>`.
    - [ ] Run the broader relevant verification command or manual check: `<command or observation>`.
    - [ ] Record useful output, generated files, screenshots, or diffs in `Artifacts and Notes`.

    ## Surprises & Discoveries

    Document unexpected behaviors, bugs, optimizations, or insights discovered during implementation. Provide concise evidence.

    - Observation: ...
      Evidence: ...

    ## Decision Log

    Record every decision made while working on the plan in the format:

    - Decision: ...
      Rationale: ...
      Date/Author: ...

    ## Validation and Acceptance

    Describe how to start or exercise the system and what to observe. Phrase acceptance as behavior, with specific inputs and outputs. If tests are involved, say "run <project's test command> and expect <N> passed; the new test <name> fails before the change and passes after".

    ## Artifacts and Notes

    Use this section for durable evidence and handoff notes, not for general progress tracking. Include only the artifacts that help someone verify, resume, or understand the work.

    - Artifact: path/to/generated-or-important-file
      Why it matters: ...

    - Evidence: command output, short diff, payload sample, screenshot path, or log excerpt
      What it proves: ...

    Keep entries short. Do not paste full logs or large diffs. If there is nothing useful to record yet, write `No artifacts captured yet.`

    ## Interfaces and Dependencies

    Be prescriptive. Name the libraries, modules, and services to use and why. Specify the types, traits/interfaces, and function signatures that must exist at the end of the milestone. Prefer stable names and paths such as `crate::module::function` or `package.submodule.Interface`. E.g.:

    In crates/foo/planner.rs, define:

        pub trait Planner {
            fn plan(&self, observed: &Observed) -> Vec<Action>;
        }

If you follow the guidance above, a single, stateless agent or a human novice can read your ExecPlan from top to bottom and produce a working, observable result. That is the bar: SELF-CONTAINED, SELF-SUFFICIENT, NOVICE-GUIDING, OUTCOME-FOCUSED.

When you revise a plan, you must ensure your changes are comprehensively reflected across all sections, including the living document sections, and you must write a note at the bottom of the plan describing the change and the reason why. ExecPlans must describe not just the what but the why for almost everything.
