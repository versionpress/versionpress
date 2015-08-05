<?php

use VersionPress\DI\VersionPressServices;
use VersionPress\Initialization\InitializationConfig;
use VersionPress\Initialization\Initializer;
use VersionPress\Utils\JsRedirect;
use VersionPress\VersionPress;

?>

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
                    href="<?php admin_url('admin.php?page=versionpress/admin/index.php') ?>">click here</a>).
            </p>
        <?php
            JsRedirect::redirect(admin_url('admin.php?page=versionpress/admin/index.php'), InitializationConfig::REDIRECT_AFTER_MS);
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
