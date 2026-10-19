# Contributing

## Branches

Branch from `main`. Do not commit on `main`.

- `feat/short-description` for new behavior
- `fix/short-description` for a bug
- `chore/short-description` for docs, CI, and repo hygiene

## Commits

Use [Conventional Commits](https://www.conventionalcommits.org/):

```
type: imperative summary
```

Use `feat`, `fix`, `docs`, `ci`, or `chore`. No trailing period on the subject.

## Pull requests

1. Open a pull request into `main` and complete the pull request template.
2. `php -l` must pass on every plugin PHP file. There is no test suite yet.
3. A maintainer merges. Do not force-push to `main`.

## Releases

Follow **Release and deploy** in `README.md`. A version bump updates `CHANGELOG.md` and the `Stable tag` in `readme.txt` in the same change.
