# Engineering: Contributing Guidelines

Guidelines for opening pull requests, submitting issues, and adhering to codebase conventions in the Eaves Droid WebApp project.

---

## 1. Branching Strategy

* `main`: Production-ready branch deployed to Railway.
* `feature/{short-description}`: New features and client dashboard additions.
* `fix/{short-description}`: Bug fixes, UI alignments, and database fixes.
* `refactor/{component}`: Architecture cleanup, engine optimization, or performance tuning.

---

## 2. Commit Message Standard

We use [Conventional Commits](https://www.conventionalcommits.org/):
* `feat(area): brief description` (e.g. `feat(telemetry): add live process cpu monitoring`)
* `fix(area): brief description` (e.g. `fix(ml): fix tab switching and token test auth`)
* `docs(area): brief description` (e.g. `docs(api): update openapi 3.0 specification`)
* `refactor(area): brief description`

---

## 3. Pull Request Requirements

Every pull request must include:
1. Clear description of the problem solved.
2. Verification steps and testing output.
3. Updated documentation in `docs/` if routes, configurations, or behavior changed.
4. Clean run of `python3 scripts/lint-docs.py .` without errors.
