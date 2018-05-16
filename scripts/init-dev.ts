import * as shell from 'shelljs';
import * as utils from "./script-utils";
import * as build from "./build";
import * as isWindows from "is-windows";
import chalk from 'chalk';

utils.exitIfNotRunFromRootDir();

//------------------------------------
utils.printTaskHeading('Checking your local environment');
if (isWindows) {
  const scriptShell = shell.exec('npm config get script-shell', { silent: true }).stdout.toString().trim();
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

// process.exit();

//------------------------------------
utils.printTaskHeading('Configuring git');
shell.exec('git config core.ignorecase false && git config core.filemode false');

//------------------------------------
utils.printTaskHeading('Installing dependencies in ext-libs');
shell.exec('composer install -d ext-libs --ignore-platform-reqs');

//------------------------------------
utils.printTaskHeading('Installing dependencies in plugins/versionpress');
shell.exec('composer install -d plugins/versionpress --ignore-platform-reqs');

//------------------------------------
utils.printTaskHeading('Installing dependencies in frontend');
shell.exec('npm i', { cwd: 'frontend' });

//------------------------------------
utils.printTaskHeading('Creating config.local.ts');
shell.cp('-n', 'frontend/src/config/config.local.sample.ts', 'frontend/src/config/config.local.ts');

//------------------------------------
utils.printTaskHeading('Building and copying frontend to the plugin');
shell.exec('npm run build', { cwd: 'frontend' });
shell.cp('-r', 'frontend/build/.', 'plugins/versionpress/admin/public/gui');

utils.printTaskHeading('init-dev done');
