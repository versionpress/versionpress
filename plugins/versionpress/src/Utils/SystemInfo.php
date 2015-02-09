<?php

namespace Utils;


use Nette\Utils\Strings;
use Symfony\Component\Process\Process;
use VersionPress\Utils\RequirementsChecker;

class SystemInfo {


    /**
     * Returns Git info
     *
     * @return array
     */
    public static function getGitInfo() {
        $info = array();

        $process = new Process("git --version");
        $process->run();

        $info['git-available'] = $process->getErrorOutput() === null;

        if ($info['git-available'] === false) {
            return $info;
        }

        $output = trim($process->getOutput());

        $match = Strings::match($output, "~git version (\\d[\\d\\.]+\\d).*~");
        $version = $match[1];

        $info['git-version'] = $version;
        $info['versionpress-min-required-version'] = RequirementsChecker::GIT_MINIMUM_REQUIRED_VERSION;
        $info['matches-min-required-version'] = RequirementsChecker::gitMatchesMinimumRequiredVersion($version, RequirementsChecker::GIT_MINIMUM_REQUIRED_VERSION);

        return $info;

    }

    /**
     * @return string Like "1.9.4" or "2.3.0"
     * @throws \Exception Throws if Git is not available
     */
    public static function getGitVersion() {
        $gitInfo = self::getGitInfo();
        if (empty($gitInfo)) {
            throw new \Exception("Git not available");
        } else {
            return $gitInfo['git-version'];
        }
    }

    /**
     * Gets info about WordPress installation
     *
     * @return array
     */
    public static function getWordPressInfo() {

        $info = array();

        $info['wp-version'] = get_bloginfo('version');
        $info['installed-plugins'] = get_plugins();
        $info['installed-themes'] = wp_get_themes();
        $info['active-plugins'] = get_option('active_plugins');
        $info['current-theme'] = wp_get_theme()->get_stylesheet();

        return $info;
    }




    /**
     * Returns phpinfo() as an array. Based on http://php.net/manual/en/function.phpinfo.php#87463.
     *
     * @return array
     */
    public static function getPhpInfo(){
        ob_start();
        phpinfo(-1);

        $pi = preg_replace(
            array('#^.*<body>(.*)</body>.*$#ms', '#<h2>PHP License</h2>.*$#ms',
                '#<h1>Configuration</h1>#',  "#\r?\n#", "#</(h1|h2|h3|tr)>#", '# +<#',
                "#[ \t]+#", '#&nbsp;#', '#  +#', '# class=".*?"#', '%&#039;%',
                '#<tr>(?:.*?)" src="(?:.*?)=(.*?)" alt="PHP Logo" /></a>'
                .'<h1>PHP Version (.*?)</h1>(?:\n+?)</td></tr>#',
                '#<h1><a href="(?:.*?)\?=(.*?)">PHP Credits</a></h1>#',
                '#<tr>(?:.*?)" src="(?:.*?)=(.*?)"(?:.*?)Zend Engine (.*?),(?:.*?)</tr>#',
                "# +#", '#<tr>#', '#</tr>#'),
            array('$1', '', '', '', '</$1>' . "\n", '<', ' ', ' ', ' ', '', ' ',
                '<h2>PHP Configuration</h2>'."\n".'<tr><td>PHP Version</td><td>$2</td></tr>'.
                "\n".'<tr><td>PHP Egg</td><td>$1</td></tr>',
                '<tr><td>PHP Credits Egg</td><td>$1</td></tr>',
                '<tr><td>Zend Engine</td><td>$2</td></tr>' . "\n" .
                '<tr><td>Zend Egg</td><td>$1</td></tr>', ' ', '%S%', '%E%'),
            ob_get_clean());

        $sections = explode('<h2>', strip_tags($pi, '<h2><th><td>'));
        unset($sections[0]);

        $pi = array();
        foreach($sections as $section){
            $n = substr($section, 0, strpos($section, '</h2>'));
            preg_match_all(
                '#%S%(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?%E%#',
                $section, $askapache, PREG_SET_ORDER);
            foreach($askapache as $m)
                // The default implementation generates notice about index 2, sometimes, so shutup operator has been added around
                // the whole right side
                $pi[$n][$m[1]] = @( (!isset($m[3])||$m[2]==$m[3])?$m[2]:array_slice($m,2) );
        }

        return $pi;
    }
}
