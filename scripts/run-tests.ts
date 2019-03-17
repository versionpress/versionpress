import * as shell from 'shelljs';
import * as utils from "./script-utils";
import { repoRoot } from "./script-utils";

const dc = 'docker-compose -f docker-compose-tests.yml';

utils.printTaskHeading('Cleaning up Docker stack incl. volumes...');
shell.exec(`${dc} down -v`, { cwd: repoRoot });

utils.printTaskHeading('Starting tests...');
shell.exec(`${dc} up -d wordpress-for-tests`, { cwd: repoRoot });

shell.exec(`${dc} run --rm wait`, { cwd: repoRoot });

shell.exec(`${dc} run --rm tests-with-wordpress ../vendor/bin/phpunit -c phpunit.xml`, { cwd: repoRoot });
shell.exec(`${dc} down`, { cwd: repoRoot });
