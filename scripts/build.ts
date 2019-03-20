import * as shell from 'shelljs';
import * as utils from './script-utils';
import * as cpy from 'cpy';
import * as fs from 'fs-extra';
import * as archiver from 'archiver';
import chalk from 'chalk';

utils.exitIfNotRunFromRootDir();

(async () => {

  //------------------------------------
  utils.printTaskHeading('Clean');
  shell.rm('-rf', 'dist/tmp');
  shell.mkdir('-p', 'dist/tmp');

  //------------------------------------
  utils.printTaskHeading('Build frontend');
  shell.exec('npm run build', { cwd: 'frontend' });
  shell.cp('-r', 'frontend/build/.', 'plugins/versionpress/admin/public/gui');

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
    cwd: 'plugins/versionpress',
    parents: true,
    dot: true,
  });

  //------------------------------------
  utils.printTaskHeading('Install production Composer dependencies');
  shell.exec('composer install -d dist/tmp --no-dev --prefer-dist --ignore-platform-reqs --optimize-autoloader');
  shell.rm('dist/tmp/composer.{json,lock}');

  //------------------------------------
  utils.printTaskHeading('Build ini-merge-driver');
  shell.exec('make build-in-docker', { cwd: './ini-merge-driver' });
  shell.exec('make install', { cwd: './ini-merge-driver' });

  //------------------------------------
  utils.printTaskHeading('Update version in plugin file');
  let version = shell.exec('git describe --tags').stdout.trim();

  const versionpressPhpPath = 'dist/tmp/versionpress.php';
  let content = await fs.readFile(versionpressPhpPath, { encoding: 'utf8' });
  content = content.replace(/^Version: (.*)$/m, 'Version: ' + version);
  await fs.writeFile(versionpressPhpPath, content, { encoding: 'utf8' });

  //------------------------------------
  utils.printTaskHeading('Produce ZIP file');
  const outFilePath = `dist/versionpress-${version}.zip`;
  const outFile = fs.createWriteStream(outFilePath);
  const archive = archiver('zip');
  archive.pipe(outFile);
  archive.directory('dist/tmp/', 'versionpress');
  await archive.finalize();

  //------------------------------------
  utils.printTaskHeading('Remove temp directory');
  shell.rm('-rf', 'dist/tmp');

  //------------------------------------
  utils.printTaskHeading('Done!');
  console.log(' ');
  console.log('Build ready: ' + chalk.white.bold.bgGreen(outFilePath));
  console.log(' ');

})();
