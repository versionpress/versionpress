import * as shell from 'shelljs';
import * as utils from "./script-utils";

const dc = 'docker-compose -f docker-compose-tests.yml';

utils.exitIfNotRunFromRootDir();

utils.printTaskHeading('Cleaning up Docker stack incl. volumes...');
shell.exec(`${dc} down -v`);

utils.printTaskHeading('Starting tests...');
shell.exec(`${dc} up -d wordpress-for-tests`);

shell.exec(`${dc} run --rm wait`);

shell.exec(`${dc} run --rm tests-with-wordpress ../vendor/bin/phpunit -c phpunit.xml`);
shell.exec(`${dc} down`);
