import * as shell from 'shelljs';
import * as utils from "./script-utils";
import { repoRoot } from "./script-utils";
import * as isWindows from "is-windows";
import chalk from 'chalk';

//------------------------------------
utils.printTaskHeading('Checking your local environment');
if (isWindows()) {
  const scriptShell = shell.exec('npm config get script-shell', { silent: true }).stdout!.toString().trim();
  if (!scriptShell.endsWith('bash.exe')) {
    console.log('');
    console.log(chalk.black.bgRedBright(`Warning!`) + chalk.redBright(` On Windows, we strongly recommend Git Bash as an npm script-shell.`));
    console.log('');
    console.log(chalk.whiteBright(`We detected your current script-shell is: ${scriptShell === 'null' ? 'cmd.exe' : scriptShell}`));
    console.log(chalk.whiteBright(`You can set a script-shell by running:`));
    console.log('');
    console.log(chalk.whiteBright(`    npm config set script-shell "c:\\Program Files\\git\\bin\\bash.exe"`));
  }
}

//------------------------------------
utils.printTaskHeading('Configuring git');
shell.exec('git config core.ignorecase false && git config core.filemode false', { cwd: repoRoot });

//------------------------------------
utils.printTaskHeading('Installing dependencies in ext-libs');
shell.exec('composer install -d ext-libs --ignore-platform-reqs', { cwd: repoRoot });

//------------------------------------
utils.printTaskHeading('Installing dependencies in plugins/versionpress');
shell.exec('composer install -d plugins/versionpress --ignore-platform-reqs', { cwd: repoRoot });

//------------------------------------
utils.printTaskHeading('Installing dependencies in frontend');
shell.exec('npm i', { cwd: `${repoRoot}/frontend` });

//------------------------------------
utils.printTaskHeading('Creating config.local.ts');
shell.cp('-n', `${repoRoot}/frontend/src/config/config.local.sample.ts`, `${repoRoot}/frontend/src/config/config.local.ts`);

//------------------------------------
utils.printTaskHeading('Building and copying frontend to the plugin');
shell.exec('npm run build', { cwd: `${repoRoot}/frontend` });
shell.cp('-r', `${repoRoot}/frontend/build/.`, `${repoRoot}/plugins/versionpress/admin/public/gui`);

utils.printTaskHeading('init-dev done');
