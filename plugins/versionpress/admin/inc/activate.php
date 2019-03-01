<?php

use VersionPress\DI\VersionPressServices;
use VersionPress\Initialization\InitializationConfig;
use VersionPress\Initialization\Initializer;
use VersionPress\Initialization\InitializerStates;
use VersionPress\Utils\JsRedirect;
use VersionPress\VersionPress;

/**
 * Function executed from VersionPress\Initialization\Initializer that is given the progress message, decides
 * whether it is suitable for output and if so, calls WP's `show_message()` function. Displaying can be forced
 * using the $forceDisplay parameter.
 *
 * @param string $progressMessage
 * @param bool $forceDisplay If true, displays the message regardless of its presence in InitializerStates enum
 */
function _vp_show_progress_message($progressMessage, $forceDisplay = false)
{

    // We currently only output messages that are defined in VersionPress\Initialization\InitializerStates
    // which captures the main progress points without too many details. Or if $forceDisplay is true.
    $shouldDisplayMessage = $forceDisplay;
    if (!$shouldDisplayMessage) {
        $initializerStatesReflection = new ReflectionClass(InitializerStates::class);
        $progressConstantValues = array_values($initializerStatesReflection->getConstants());
        $shouldDisplayMessage = in_array($progressMessage, $progressConstantValues);
    }

    if ($shouldDisplayMessage) {
        /** @noinspection PhpParamsInspection */
        /** @noinspection PhpInternalEntityUsedInspection */
        show_message($progressMessage);
    }
}
?>

<style>
    .vp-index .welcome-panel {
        padding-bottom: 23px;
        margin-top: 20px;
    }

    .vp-index .welcome-panel p {
        color: inherit;
    }

    .vp-index .welcome-panel .about-description {
        margin: 23px 0 10px;
    }

    .vp-index .welcome-panel ul {
        list-style: none;
        padding-left: 40px;
    }

    .vp-index .welcome-panel ul li {
        position: relative;
    }

    .vp-index .welcome-panel ul .icon {
        position: absolute;
        left: -25px;
        top: 6px;
    }

    .initialization-progress p {
        margin: 1px 0;
    }

    .initialization-done {
        font-size: 1.2em;
        font-weight: bold;
    }
</style>

<div class="welcome-panel">

    <div class="welcome-panel-content">

        <h3>VersionPress Activation</h3>


        <p class="about-description">Setting things up for you. It may take a while, please be patient.</p>

        <div class="initialization-progress">
            <?php

            // Set the env name in wp-config.php
            if (isset($_GET['envname']) && \VersionPress\Utils\WorkflowUtils::isCloneNameValid($_GET['envname'])) {
                $envName = $_GET['envname'];
            } else {
                $envName = 'default';
            }

            $wpConfigPath = \VersionPress\Utils\WordPressMissingFunctions::getWpConfigPath();
            $wpConfigEditor = new \VersionPress\Utils\WpConfigEditor($wpConfigPath, false);
            $wpConfigEditor->updateConfigConstant('VP_ENVIRONMENT', $envName);

            _vp_show_progress_message("Environment set to '$envName'", true);


            // Do the initialization
            global $versionPressContainer;
            /** @var Initializer $initializer */
            $initializer = $versionPressContainer->resolve(VersionPressServices::INITIALIZER);
            $initializer->onProgressChanged[] = '_vp_show_progress_message';

            vp_load_hooks_files();
            $initializer->initializeVersionPress();


            $successfullyInitialized = VersionPress::isActive();

            ?>
        </div>

        <?php
        if ($successfullyInitialized) { ?>
            <p class="initialization-done">All done, we're now redirecting you (or <a
                    href="<?php menu_page_url('versionpress', false) ?>">click here</a>).
            </p>
            <?php
            JsRedirect::redirect(menu_page_url('versionpress', false), InitializationConfig::REDIRECT_AFTER_MS);
        } else { ?>
            <p class="initialization-done">
                Ouch. The initialization took too long and was terminated by the server.<br>Please increase <a href="http://php.net/manual/en/info.configuration.php#ini.max-execution-time" target="_blank">maximal execution time</a> and <a href="<?php admin_url('admin.php?page=versionpress/admin/index.php&init_versionpress'); ?>">try it again</a>.
            </p>
            <?php
        }
        ?>

    </div>

</div>
