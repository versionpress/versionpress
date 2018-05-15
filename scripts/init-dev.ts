import * as shell from 'shelljs';
import * as utils from "./script-utils";
import * as build from "./build";

utils.exitIfNotRunFromRootDir();

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
