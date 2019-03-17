# VersionPress dev scripts

Scripts that overgrew the one-liners in `package.json`.

## Dev setup

`npm install` is run as part of root's post-install script so you should be fine.

Scripts are meant to be run from repo root, like this:

```
node -r ./scripts/node_modules/ts-node/register scripts/build.ts
```

## Debugging scripts

To debug the scripts, add `--inspect-brk` or use the predefined `debug-script` task, e.g.:

```
npm run debug-script scripts/build.ts
```

Then in VSCode, create a "Node attach" configuration and run it.
