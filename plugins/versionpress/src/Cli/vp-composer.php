<?php
// NOTE: VersionPress must be fully activated for these commands to be available

// WORD-WRAPPING of the doc comments: 75 chars for option description, 90 chars for everything else,
// see http://wp-cli.org/docs/commands-cookbook/#longdesc.
// In this source file, wrap long desc at col 97 and option desc at col 84.

namespace VersionPress\Cli;

use VersionPress\Utils\Process;
use VersionPress\VersionPress;
use WP_CLI;
use WP_CLI_Command;

/**
 * VersionPress CLI commands for Composer scripts.
 */
class VPComposerCommand extends WP_CLI_Command
{

    private $pluginsThemesTransient = 'vp_composer_plugins_themes';

    /**
     * @subcommand prepare-for-composer-changes
     */
    public function prepareForComposerChanges($args, $assoc_args)
    {
        set_transient($this->pluginsThemesTransient, $this->getPackages());
    }

    /**
     * Commits all changes made by Composer.
     *
     * @subcommand commit-composer-changes
     */
    public function commitComposerChanges($args, $assoc_args)
    {
        if (!VersionPress::isActive()) {
            WP_CLI::error('VersionPress is not active. Changes will be not committed.');
        }

        $pluginsAndThemesBeforeUpdate = get_transient($this->pluginsThemesTransient);
        delete_transient($this->pluginsThemesTransient);
        $currentPluginsAndThemes = $this->getPackages();

        $plugins = array_merge($pluginsAndThemesBeforeUpdate['plugins'], $currentPluginsAndThemes['plugins']);
        $themes = array_merge($pluginsAndThemesBeforeUpdate['themes'], $currentPluginsAndThemes['themes']);

        $changes = $this->detectChanges();
        $installedPackages = $changes['installed'];
        $removedPackages = $changes['removed'];
        $updatedPackages = $changes['updated'];

        $this->forceRelatedActions('install', $installedPackages, $plugins, $themes);
        $this->forceRelatedActions('delete', $removedPackages, $plugins, $themes);
        $this->forceRelatedActions('update', $updatedPackages, $plugins, $themes);
    }

    private function detectChanges()
    {
        $currentComposerLock = file_get_contents(VP_PROJECT_ROOT . '/composer.lock');

        $process = new Process(VP_GIT_BINARY . ' show HEAD:composer.lock', VP_PROJECT_ROOT);
        $process->run();

        $previousComposerLock = $process->getOutput();

        $currentPackages = $this->getPackagesFromLockFile($currentComposerLock);
        $previousPackages = $this->getPackagesFromLockFile($previousComposerLock);

        $installedPackages = array_diff_key($currentPackages, $previousPackages);
        $removedPackages = array_diff_key($previousPackages, $currentPackages);

        $packagesWithChangedVersion = array_filter(
            array_intersect_key($currentPackages, $previousPackages),
            function ($package) use ($previousPackages) {
                return $package['version'] !== $previousPackages[$package['name']]['version'];
            }
        );

        return [
            'installed' => $installedPackages,
            'removed' => $removedPackages,
            'updated' => $packagesWithChangedVersion,
        ];
    }

    private function getPackagesFromLockFile($lockFileContent)
    {
        $lockFile = json_decode($lockFileContent, true);
        return array_combine(
            array_column($lockFile['packages'], 'name'),
            array_map(function ($package) {
                return [
                    'name' => $package['name'],
                    'version' => $package['version'],
                    'type' => $package['type'],
                    'homepage' => @$package['homepage'] ?: null,
                ];
            }, $lockFile['packages'])
        );
    }

    private function getPackages()
    {
        return ['plugins' => get_plugins(), 'themes' => wp_get_themes()];
    }

    private function getPluginFileAndName($fullPackageName, $plugins)
    {
        list($vendor, $packageName) = explode('/', $fullPackageName);

        foreach ($plugins as $pluginFile => $plugin) {
            list($pluginDirectory) = explode('/', $pluginFile, 2);

            if ($packageName === $pluginDirectory) {
                return ['plugin-file' => $pluginFile, 'name' => $plugin['Name']];
            }
        }

        return null;
    }

    private function getThemeStylesheetAndName($fullPackageName, $themes)
    {
        list($vendor, $packageName) = explode('/', $fullPackageName);

        foreach ($themes as $themeName => $theme) {
            /** @var $theme \WP_Theme */
            if ($packageName === $themeName) {
                return ['stylesheet' => $theme->get_stylesheet(), 'name' => $theme->get('Name')];
            }
        }

        return null;
    }

    /**
     * @param string $action install or delete or update
     * @param array $packages
     * @param array $plugins
     * @param array $themes
     */
    private function forceRelatedActions($action, $packages, $plugins, $themes)
    {
        foreach ($packages as $package) {
            if ($package['type'] === 'wordpress-plugin') {
                $fileAndName = $this->getPluginFileAndName($package['name'], $plugins);
                do_action('vp_plugin_changed', $action, $fileAndName['plugin-file'], $fileAndName['name']);
            } elseif ($package['type'] === 'wordpress-theme') {
                $stylesheetAndName = $this->getThemeStylesheetAndName($package['name'], $themes);
                do_action('vp_theme_changed', $stylesheetAndName['stylesheet'], $stylesheetAndName['name']);
            } elseif ($package['type'] === 'wordpress-core') {
                do_action('vp_wordpress_updated', $package['version']);
            } else {
                $composerFiles = [
                    ["type" => "path", "path" => VP_PROJECT_ROOT . '/composer.json'],
                    ["type" => "path", "path" => VP_PROJECT_ROOT . '/composer.lock']
                ];

                vp_force_action('composer', $action, $package['name'], [], $composerFiles);
            }
        }
    }
}

if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('vp-composer', VPComposerCommand::class);
}
