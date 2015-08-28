<?php

use VersionPress\DI\VersionPressServices;
use VersionPress\Initialization\InitializationConfig;
use VersionPress\Initialization\Initializer;
use VersionPress\Utils\JsRedirect;
use VersionPress\VersionPress;

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
            global $versionPressContainer;

            /**
             * @var Initializer $initializer
             */
            $initializer = $versionPressContainer->resolve(VersionPressServices::INITIALIZER);
            $initializer->onProgressChanged[] = '_vp_show_progress_message';
            $initializer->initializeVersionPress(); // This is a long-running operation

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
                Ouch. The initialization took too long and was terminated by the server.<br>
                Please increase
                <a href="http://php.net/manual/en/info.configuration.php#ini.max-execution-time" target="_blank">maximal execution time</a>
                and <a href="<?php admin_url('admin.php?page=versionpress/admin/index.php&init_versionpress'); ?>">try it again</a>.
            </p>
        <?php } ?>

    </div>

</div>
