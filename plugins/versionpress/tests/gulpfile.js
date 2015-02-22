var gulp = require('gulp');
var phpunit = require('gulp-phpunit');
var seleniumPlease = require('selenium-please');
var chalk = require('chalk');
var tcpPortUsed = require('tcp-port-used');
var argv = require('yargs').argv;

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
        console.log(chalk.cyan('Usage:') + ' ' + chalk.bold('gulp run-tests [--force-setup[=before-suite|before-class]] [--git=<path>]'));
        console.log('');
        console.log(chalk.cyan('Options:'));
        console.log('  ' + chalk.bold('--force-setup[=before-suite|before-class]'));
        console.log('    Force WP site refresh before suite or every Selenium test class. No value = same as \'before-suite\'.');
        console.log('');
        console.log('  ' + chalk.bold('--git=<path>'));
        console.log('    Optionally pass Git executable to use for the tests. Allows testing against more Git versions.');
        console.log('');
        console.log(chalk.cyan('Notes:'));
        console.log('');
        console.log(' - Make sure that ' + chalk.yellow('test-config.neon') + ' is configured properly');
        console.log(' - Tests defined in ' + chalk.yellow('phpunit.xml') + ' will be run');
        console.log(' - Port ' + chalk.yellow('4444') + ' must be available' + portStatus);
        console.log(' - ' + chalk.yellow('Java') + ' has to be installed and in the PATH');
        console.log(' - Selenium Server will be downloaded and run automatically');
        console.log(' - Firefox defined in test-config.neon will be used');
        console.log('');

    })

});

gulp.task('run-tests', function(cb) {


    seleniumPlease({

        selenium: {
            // see http://selenium-release.storage.googleapis.com/index.html
            url: 'http://selenium-release.storage.googleapis.com/2.44/selenium-server-standalone-2.44.0.jar',
            file: './node_modules/.bin/selenium-server-standalone-2.44.0.jar'
        },

        drivers: [], // in other words, use phantomjs only (see selenium-please/libs/run.js)

        log: './node_modules/.bin/selenium.log',

        debugJava: true,

        port: 4444

    }, function(err, selenium) {

        if (argv['force-setup'] !== undefined) {

            if (argv['force-setup'] === true) {
                // just --force-setup without any value, default to before-suite
                argv['force-setup'] = "before-suite";
            }
            process.env['VP_FORCE_SETUP'] = argv['force-setup'];
        }


        if (argv['git'] !== undefined) {
            process.env['VP_GIT'] = argv['git'];
        }

        gulp.src('phpunit.xml')
            .pipe(phpunit('..\\vendor\\bin\\phpunit.bat', {
                logTap: "./phpunit-log.tap.txt",
                testdoxText: "./phpunit-log.textdox.txt",
                verbose: true
            }))
            .on('end', function() {
                console.log("Tests done");
                selenium.kill();
                cb();
            })
            .on('error', function(err) {
                console.log("Test failed");
                selenium.kill();
                cb();
            });

    });
});

