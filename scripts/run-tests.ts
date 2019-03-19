import * as shell from 'shelljs';
import * as utils from './script-utils';
import { repoRoot } from './script-utils';
import * as arg from 'arg';

const dc = 'docker-compose -f docker-compose-tests.yml';
const wait = (target: string) => `${dc} run --rm -e TARGETS=${target} wait`;

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
    --testsuite         Testsuite from phpunit.xml to run. Can be repeated
                        multiple times.
    --with-wordpress    Start WordPress & MySQL containers. WIP: is implied
                        from --testsuite in some cases.
    -h, --help          Show help.

  Examples
    $ run-tests --testsuite Unit
`);
  process.exit();
}

if (!args['--testsuite']) {
  // All tests will be run, we need to start WordPress
  args['--with-wordpress'] = true;
}

if (args['--with-wordpress']) {
  utils.printTaskHeading('Cleaning up Docker containers and volumes...');
  shell.exec(`${dc} down -v`, { cwd: repoRoot });

  utils.printTaskHeading('Starting MySQL...');
  shell.exec(`${dc} up -d mysql-for-tests`, { cwd: repoRoot });
  shell.exec(wait('mysql-for-tests:3306'), { cwd: repoRoot });

  utils.printTaskHeading('Starting WordPress...');
  shell.exec(`${dc} up -d wordpress-for-tests`, { cwd: repoRoot });
  shell.exec(wait('wordpress-for-tests:80'), { cwd: repoRoot });
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
