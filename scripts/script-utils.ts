import * as fs from 'fs-extra';
import * as shell from 'shelljs';
import chalk from 'chalk';

/**
 * All scripts are assumed to run from the repo root so that the paths can look like
 * 'plugins/versionpress' or 'dist'. Call this function as the first thing in scripts.
 */
export function exitIfNotRunFromRootDir() {
  if (!fs.existsSync('.github')) {
    console.error('init-dev must be run from repo root, cwd was ' + process.cwd());
    process.exit(1);
  }
}

/**
 * Makes the console output a little nicer.
 */
export function printTaskHeading(heading: string) {
  console.log('');
  console.log(chalk.gray('> ') + chalk.cyan(heading));
}
