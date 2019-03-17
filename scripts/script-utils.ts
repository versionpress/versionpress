import chalk from 'chalk';
import * as path from 'path';

export const repoRoot = path.resolve(__dirname, '..');

export function printTaskHeading(heading: string) {
  console.log('');
  console.log(chalk.gray('> ') + chalk.cyan(heading));
}
