# Code Quality Standards

This document outlines coding standards and practices for this project.

## General Principles
- Prefer clarity and simplicity over cleverness.
- Keep changes minimal and focused on the task.
- Follow existing conventions for naming and structure.

## PHP/Laravel
- Use controllers, models, and Blade templates consistently.
- Validate request data using Form Requests where possible.
- Keep Blade views clean; push complex logic into controllers/view models.
- Use Eloquent relationships and eager loading to avoid N+1 issues.
- Apply caching judiciously for expensive queries.

## JavaScript
- Use the centralized `Helpers` namespace for shared utilities.
- Avoid duplicating inline formatting/validation logic; rely on `helpers.js`.
- Keep DOM selectors stable via IDs and semantic structure.

## Views & UI
- Map status enums to UI labels consistently (see Technical Documentation).
- Use consistent Bootstrap classes for badges and form controls.
- Ensure accessible labels and placeholders in forms.

## Testing
- Favor feature tests for user-facing flows.
- Mock external dependencies and avoid brittle assertions.
- Keep tests deterministic; seed data where appropriate.

## Documentation
- Update `docs/` when making changes that affect behavior or configuration.
- Summarize changes in `implementation-summary.md` for major updates.

## Review & CI
- Run linters/formatters if configured.
- Ensure all tests pass locally before pushing.
- Perform code reviews with attention to security and performance.