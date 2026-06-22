# Role Skills Design

## Goal

Create three generic reusable agent skills for any repository:

- `architect` for planning
- `senior-software-engineer` for implementation
- `qc-qa-specialist` for testing, TDD, and verification

## Constraints

- Skills must be generic, not `resumind`-specific.
- Skills should use balanced guidance: strong defaults, but adaptable.
- Skills stay independent, but cross-reference each other.
- `AGENTS.md` must instruct agents when to use these skills.

## Chosen Approach

Use three role skills with explicit boundaries and handoff points.

Each skill will define:

- when to trigger
- what it owns
- what it should deliver
- what it must not do
- when to hand off to sibling skills

## Behavior

### `architect`

- Used before non-trivial implementation, refactors, feature expansion, or ambiguous bug work
- Produces framing, options, recommendation, execution slices, risks, and verification strategy
- Avoids jumping into production code while scope remains unclear

### `senior-software-engineer`

- Used for approved implementation, focused bug fixes, and repo-conventional code edits
- Reads current patterns first, keeps diffs intentional, and prefers simple maintainable designs
- Escalates back to planning when scope becomes unclear

### `qc-qa-specialist`

- Used for TDD, regression tests, bug reproduction, and final verification
- Emphasizes failure-first validation, red-green-refactor, regression coverage, and evidence-based signoff
- Can be invoked before code changes and again before completion

## `AGENTS.md` Routing Update

Add a dedicated role-skills section that tells agents:

- use `architect` first for new features, multi-file work, unclear requirements, risky refactors, and bugs without confirmed root cause
- use `senior-software-engineer` for approved implementation and focused code changes
- use `qc-qa-specialist` before and after behavior changes for test design, TDD, regression coverage, and verification
- skills are independent, but can be combined as task shape requires

## Expected Outcome

Agents get clearer routing for plan vs implement vs verify work, with reusable generic skill definitions that can apply beyond this repository.
