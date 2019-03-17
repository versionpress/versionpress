import * as shell from 'shelljs';
import * as utils from './script-utils';
import { repoRoot } from './script-utils';
import * as arg from 'arg';

const dc = 'docker-compose -f docker-compose-tests.yml';

const args = arg({
  '--help': Boolean,
  '--with-wordpress': Boolean,
  '--testsuite': [String],
  '-h': '--help',
});

if (args['--help']) {
  console.log(`
  Usage
    $ run-tests ...

  Options
    --testsuite           Testsuite from phpunit.xml to run. Can be repeated.
    --with-wordpress      Start WordPress & MySQL containers.
    -h, --help            Show help.

  Examples
    $ run-tests --testsuite Unit
`);
  process.exit();
}

if (args['--with-wordpress']) {
  utils.printTaskHeading('Cleaning up Docker containers and volumes...');
  shell.exec(`${dc} down -v`, { cwd: repoRoot });

  utils.printTaskHeading('Starting WordPress and MySQL for tests...');
  shell.exec(`${dc} up -d wordpress-for-tests`, { cwd: repoRoot });
  shell.exec(`${dc} run --rm wait`, { cwd: repoRoot });
}

utils.printTaskHeading('Running tests...');

const containerToUse = args['--with-wordpress'] ? 'tests-with-wordpress' : 'tests';
const customTestSuite = args['--testsuite'] ? args['--testsuite'].map(suite => `--testsuite ${suite}`).join(' ') : '';

shell.exec(`${dc} run --rm ${containerToUse} ../vendor/bin/phpunit -c phpunit.xml ${customTestSuite}`, {
  cwd: repoRoot,
});

if (args['--with-wordpress']) {
  utils.printTaskHeading('Stopping containers...');
  shell.exec(`${dc} down`, { cwd: repoRoot });
}
