import * as shell from 'shelljs';
import cpy from 'cpy';
import * as fs from 'fs-extra';
import * as archiver from 'archiver';
import chalk from 'chalk';
import * as utils from './script-utils';
import { repoRoot } from './script-utils';

(async () => {

  //------------------------------------
  utils.printTaskHeading('Clean');
  shell.rm('-rf', `${repoRoot}/dist/tmp`);
  shell.mkdir('-p', `${repoRoot}/dist/tmp`);

  //------------------------------------
  utils.printTaskHeading('Build frontend');
  shell.exec('npm run build', { cwd: `${repoRoot}/frontend` });
  shell.cp('-r', `${repoRoot}/frontend/build/.`, `${repoRoot}/plugins/versionpress/admin/public/gui`);

  //------------------------------------
  utils.printTaskHeading('Copy files to dist/tmp');
  const filesToCopy = [
    '.',
    // everything except...
    '!vendor',
    '!tests',
    '!.idea',
    '!.gitignore',
    '!ruleset.xml',
  ];

  await cpy(filesToCopy, '../../dist/tmp', {
    cwd: `${repoRoot}/plugins/versionpress`,
    parents: true,
    dot: true,
  });

  //------------------------------------
  utils.printTaskHeading('Install production Composer dependencies');
  shell.exec('composer install -d dist/tmp --no-dev --prefer-dist --ignore-platform-reqs --optimize-autoloader', { cwd: `${repoRoot}`} );
  shell.rm(`${repoRoot}/dist/tmp/composer.{json,lock}`);

  //------------------------------------
  utils.printTaskHeading('Update version in plugin file');
  let version = shell.exec('git describe --tags', { cwd: `${repoRoot}` }).stdout!.toString().trim();

  const versionpressPhpPath = `${repoRoot}/dist/tmp/versionpress.php`;
  let content = await fs.readFile(versionpressPhpPath, { encoding: 'utf8' });
  content = content.replace(/^Version: (.*)$/m, 'Version: ' + version);
  await fs.writeFile(versionpressPhpPath, content, { encoding: 'utf8' });

  //------------------------------------
  utils.printTaskHeading('Produce ZIP file');
  const outFilePath = `${repoRoot}/dist/versionpress-${version}.zip`;
  const outFile = fs.createWriteStream(outFilePath);
  const archive = archiver('zip');
  archive.pipe(outFile);
  archive.directory(`${repoRoot}/dist/tmp/`, 'versionpress');
  await archive.finalize();

  //------------------------------------
  utils.printTaskHeading('Remove temp directory');
  shell.rm('-rf', `${repoRoot}/dist/tmp`);

  //------------------------------------
  utils.printTaskHeading('Done!');
  console.log(' ');
  console.log('Build ready: ' + chalk.white.bold.bgGreen(outFilePath));
  console.log(' ');

})();
