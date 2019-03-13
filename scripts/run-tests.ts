import * as shell from 'shelljs';
import * as utils from "./script-utils";

const dc = 'docker-compose -f docker-compose-tests.yml';

utils.exitIfNotRunFromRootDir();

utils.printTaskHeading('Cleaning up Docker stack incl. volumes...');
shell.exec(`${dc} down -v`);

utils.printTaskHeading('Starting tests...');
shell.exec(`${dc} up -d wordpress-for-tests`);

// Try commenting out the following line; the test should then fail as they won't wait for MySQL
shell.exec(`${dc} run --rm wait`);

shell.exec(`${dc} -f docker-compose-tests.yml run --rm tests-with-wordpress ../vendor/bin/phpunit -c phpunit-temp.xml`);
shell.exec(`${dc} down`);
