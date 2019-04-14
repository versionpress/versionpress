# VersionPress dev scripts

Scripts that have outgrown the one-liners in `package.json`.

## Dev setup

`npm install` is run as part of root's post-install script so you should be fine.

Scripts can be run during development like this:

```
node -r ts-node/register build.ts
```

The repo-root `package.json` scripts then call them like this:

```
node -r ./scripts/node_modules/ts-node/register scripts/build.ts
```

## Debugging scripts

Use a `npm run debug-script -- ...` or manually add `--inspect-brk`:

```
node -r ts-node/register --inspect-brk build.ts
```

In VSCode, use the "Node attach" configuration.

## About the `changelog` script

The script is used when [preparing a release](../docs/content/en/developer/development-process.md#release-process).

How to use it:

1. Create a new [personal access token](https://github.com/settings/tokens) on GitHub. It only needs the `public_repo` scope.
2. Copy `.env.example` to `.env` and put the token there.
3. Run the tool like this: `npm run changelog -- 4.0-beta..master`
