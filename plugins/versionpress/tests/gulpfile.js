var gulp = require('gulp');
var chalk = require('chalk');
var tcpPortUsed = require('tcp-port-used');
var argv = require('yargs').argv;
var path = require('path');
var childProcess = require('child_process');
var fs = require('fs');
var selenium = require('selenium-standalone');

// Run this task to get the help
gulp.task('default', function() {

    tcpPortUsed.check(4444).then(function(isInUse) {

        var portStatus = '';
        if (isInUse) {
            portStatus = chalk.red(' (currently in use)');
        } else {
            portStatus = ' (currently is)';
        }

        console.log('');
        console.log(chalk.cyan('Usage:') + ' ' + chalk.bold('gulp run-tests [--force-setup[=before-suite|before-class]]'));
        console.log('');
        console.log(chalk.cyan('Options:'));
        console.log('  ' + chalk.bold('--force-setup[=before-suite|before-class]'));
        console.log('    Force WP site refresh before suite or every Selenium test class. No value = same as \'before-suite\'.');
        console.log('');
        console.log(chalk.cyan('Notes:'));
        console.log('');
        console.log(' - Make sure that ' + chalk.yellow('test-config.yml') + ' is configured properly');
        console.log(' - Tests defined in ' + chalk.yellow('phpunit.xml') + ' will be run');
        console.log(' - Port ' + chalk.yellow('4444') + ' must be available' + portStatus);
        console.log(' - ' + chalk.yellow('Java') + ' has to be installed and in the PATH');
        console.log(' - Selenium Server will be downloaded and run automatically');
        console.log(' - Firefox defined in test-config.yml will be used');
        console.log('');

    })

});

gulp.task('run-tests', function(cb) {
    // check for more recent versions of selenium here:
    // http://selenium-release.storage.googleapis.com/index.html
    var seleniumVersion = '2.47.1';

    selenium.install({
        version: seleniumVersion,
        baseURL: 'http://selenium-release.storage.googleapis.com',
        drivers: {},
        logger: function(message) {
            console.log(message)
        }
    }, function(err) {
        if (err) return cb(err);

        selenium.start({
            version: seleniumVersion
        }, function(err, child) {
            if (err) return cb(err);

            if (argv['force-setup'] !== undefined) {

                if (argv['force-setup'] === true) {
                    // just --force-setup without any value, default to before-suite
                    argv['force-setup'] = "before-suite";
                }
                process.env['VP_FORCE_SETUP'] = argv['force-setup'];
            }

            var phpUnitCmd = fs.realpathSync(path.join('..', 'vendor', 'bin', 'phpunit'));
            var isWindows = (process.platform.lastIndexOf('win') === 0);
            if (isWindows) {
                phpUnitCmd += ".bat";
            }
            var phpUnitCmdArgs = [
                "--log-tap=./phpunit-log.tap.txt",
                "--testdox-text=./phpunit-log.testdox.txt",
                "--verbose",
                "--colors"
            ];

            var outFile = fs.createWriteStream('phpunit-log.txt');


            var phpunit = childProcess.spawn(phpUnitCmd, phpUnitCmdArgs);
            phpunit.stdout.on('data', function(data) {
                process.stdout.write(data);
                outFile.write(data);
            });
            phpunit.stdout.on('end', function(data) {
                outFile.end();
                child.kill();
                cb();
            });

            phpunit.on('error', function(err) {
                console.log("Error");
                console.log(err);
                child.kill();
                cb();
            });
        })
    })

});

