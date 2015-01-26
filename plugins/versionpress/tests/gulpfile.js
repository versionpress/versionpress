var gulp = require('gulp');
var phpunit = require('gulp-phpunit');
var seleniumPlease = require('selenium-please');
var chalk = require('chalk');

// Run this task to get the help
gulp.task('default', function() {
    console.log('');
    console.log(chalk.cyan('Usage:') + ' ' + chalk.bold('gulp run-tests'));
    console.log('');
    console.log(' - Make sure that ' + chalk.yellow('test-config.ini') + ' is configured properly');
    console.log(' - Tests defined in ' + chalk.yellow('phpunit.xml') + ' will be run');
    console.log(' - ' + chalk.yellow('Java') + ' has to be installed and in the PATH');
    console.log(' - Selenium Server will be downloaded and run automatically');
    console.log(' - Firefox defined in test-config.ini will be used');
    console.log('');
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

        gulp.src('phpunit.xml')
            .pipe(phpunit('..\\vendor\\bin\\phpunit.bat', {verbose: true}))
            .on('end', function() {
                console.log("Tests done");
                selenium.kill();
            })
            .on('error', function(err) {
                console.log("Test failed");
                selenium.kill();
            });

    });
});

