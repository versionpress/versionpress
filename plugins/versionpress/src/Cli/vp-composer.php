<?php
// NOTE: VersionPress must be fully activated for these commands to be available

// WORD-WRAPPING of the doc comments: 75 chars for option description, 90 chars for everything else,
// see http://wp-cli.org/docs/commands-cookbook/#longdesc.
// In this source file, wrap long desc at col 97 and option desc at col 84.

namespace VersionPress\Cli;

use VersionPress\ChangeInfos\PluginChangeInfo;
use VersionPress\ChangeInfos\ThemeChangeInfo;
use VersionPress\ChangeInfos\WordPressUpdateChangeInfo;
use VersionPress\DI\VersionPressServices;
use VersionPress\Git\Committer;
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
        global $versionPressContainer;

        if (!VersionPress::isActive()) {
            WP_CLI::error('VersionPress is not active. Changes will be not committed.');
        }

        $pluginsAndThemesBeforeUpdate = get_transient($this->pluginsThemesTransient);
        delete_transient($this->pluginsThemesTransient);
        $currentPluginsAndThemes = $this->getPackages();

        $plugins = array_merge($pluginsAndThemesBeforeUpdate['plugins'], $currentPluginsAndThemes['plugins']);
        $themes = array_merge($pluginsAndThemesBeforeUpdate['themes'], $currentPluginsAndThemes['themes']);

        /** @var Committer $committer */
        $committer = $versionPressContainer->resolve(VersionPressServices::COMMITTER);
        $changes = $this->detectChanges();
        $installedPackages = $changes['installed'];
        $removedPackages = $changes['removed'];
        $updatedPackages = $changes['updated'];

        $changeInfos = array_values(array_filter(array_merge(
            array_map(function ($package) use ($plugins, $themes) {
                if ($package['type'] === 'wordpress-plugin') {
                    return $this->createPluginChangeInfo('install', $package['name'], $plugins);
                }

                if ($package['type'] === 'wordpress-theme') {
                    return $this->createThemeChangeInfo('install', $package['name'], $themes);
                }

                return null;

            }, $installedPackages),
            array_map(function ($package) use ($plugins, $themes) {
                if ($package['type'] === 'wordpress-plugin') {
                    return $this->createPluginChangeInfo('delete', $package['name'], $plugins);
                }

                if ($package['type'] === 'wordpress-theme') {
                    return $this->createThemeChangeInfo('delete', $package['name'], $themes);
                }

                return null;

            }, $removedPackages),
            array_map(function ($package) use ($plugins, $themes) {
                if ($package['type'] === 'wordpress-plugin') {
                    return $this->createPluginChangeInfo('update', $package['name'], $plugins);
                }

                if ($package['type'] === 'wordpress-theme') {
                    return $this->createThemeChangeInfo('update', $package['name'], $themes);
                }

                if ($package['type'] === 'wordpress-core') {
                    return new WordPressUpdateChangeInfo($package['version']);
                }

                return null;

            }, $updatedPackages)
        )));

        foreach ($changeInfos as $changeInfo) {
            $committer->forceChangeInfo($changeInfo);
        }

        $committer->commit();
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

    private function createPluginChangeInfo($action, $fullPackageName, $plugins)
    {
        list($vendor, $packageName) = explode('/', $fullPackageName);

        foreach ($plugins as $pluginFile => $plugin) {
            list($pluginDirectory) = explode('/', $pluginFile, 2);

            if ($packageName === $pluginDirectory) {
                return new PluginChangeInfo($pluginFile, $action, $plugin['Name']);
            }
        }

        return null;
    }

    private function createThemeChangeInfo($action, $fullPackageName, $themes)
    {
        list($vendor, $packageName) = explode('/', $fullPackageName);

        foreach ($themes as $themeName => $theme) {
            /** @var $theme \WP_Theme */
            if ($packageName === $themeName) {
                return new ThemeChangeInfo($theme->get_template(), $action, $theme->get('Name'));
            }
        }

        return null;
    }
}

if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('vp-composer', VPComposerCommand::class);
}
