<?php

use VersionPress\DI\VersionPressServices;
use VersionPress\Git\GitRepository;
use VersionPress\Utils\Markdown;
use VersionPress\Utils\RequirementsChecker;

?>

<script>

jQuery(document).ready(function($) {
    $('#activate-versionpress-btn').click(function (e) {
        var envname = $('#envname').val();

        // Quick and dirty validation; the canonical regexp is in WorkflowUtils::isCloneNameValid().
        if (!/^[a-zA-Z0-9-_]+$/.test(envname)) {
            alert('Please use letters, numbers, \'-\' and \'_\' for environment name only');
            e.preventDefault();
        }

        $(this).attr('href', this.href + '&envname=' + encodeURIComponent(envname));
    });
});

</script>

<div class="welcome-panel vp-activation-panel">

    <div class="welcome-panel-content">

        <h3>Welcome to VersionPress!</h3>

        <p class="about-description">VersionPress needs a one-time activation step that initializes its internal storage. <strong>This step is resource-intensive and might take a while</strong> if your site has many entities (posts, comments etc.). The site will be put in maintanenance mode until it finishes.</p>

        <div class="checks-and-warnings">

            <div class="left">
                <h4>System requirements check</h4>

                <ul class="vp-requirements-check">
                    <?php
                    global $versionPressContainer;
                    /** @var GitRepository $repository */
                    $repository = $versionPressContainer->resolve(VersionPressServices::REPOSITORY);
                    $database = $versionPressContainer->resolve(VersionPressServices::DATABASE);
                    $schema = $versionPressContainer->resolve(VersionPressServices::DB_SCHEMA);

                    $requirementsChecker = new RequirementsChecker($database, $schema);
                    $report = $requirementsChecker->getRequirements();

                    foreach ($report as $requirement) {
                        $iconClass = $requirement["fulfilled"] ? "icon-checkmark" : "icon-warning";
                        ?>
                        <li>
                            <span class="icon <?php echo esc_attr($iconClass); ?>"></span>
                            <?php echo esc_html($requirement["name"]); ?>
                            <p class="<?php echo $requirement["fulfilled"] ? 'closed' : 'open'; ?>">
                                <?php echo Markdown::transform($requirement["help"]); ?>
                            </p>
                        </li>
                        <?php
                    }

                    if ($requirementsChecker->isWithoutCriticalErrors() && $repository->isVersioned()) {
                        ?>
                        <li>
                            <span class="icon icon-warning"></span>
                            Note: This website is already versioned in Git (the repository is either your custom or has been created by a previous installation of VersionPress).
                            VersionPress will add some rules into `.gitignore` and install a custom merge driver for its own files.
                            It is not a problem for VersionPress, just be sure that you know what you are doing.
                        </li>
                        <?php
                    }
                    ?>
                </ul>

                <div><a href="<?php echo esc_url(admin_url('admin.php?page=versionpress/admin/system-info.php')) ?>">View full system info</a><?php if (!$requirementsChecker->isWithoutCriticalErrors()) { ?>, <a href="https://github.com/versionpress/support">get support on GitHub</a><?php } ?></div>

            </div>

            <div class="right">
                <h4>General notes</h4>

                <ul>
                    <li>
                        <span class="icon icon-notification"></span>
                        You are activating an <strong>Early Access version</strong>. If you encounter any issues please let us know <a href="https://github.com/versionpress/support">on GitHub</a>. <a href="http://docs.versionpress.net/en/getting-started/about-eap">Learn more</a>.
                    </li>
                    <li>
                        <span class="icon icon-notification"></span>
                        Be careful when using <strong>third-party plugins</strong>. Some of them work fine, some might be problematic in combination with VersionPress. <a href="http://docs.versionpress.net/en/feature-focus/external-plugins">Learn more</a>.
                    </li>
                    <li>
                        <span class="icon icon-notification"></span>
                        <strong>Have a backup</strong>. Seriously.
                    </li>
                </ul>

                <h4>Name your environment</h4>

                <ul>
                    <li>
                        <span class="dashicons dashicons-info icon" style="color: #5b9dd9; font-size: 1.2em; left: -28px;"></span>
                        E.g., <code>production</code>, <code>dev</code> or <code>my-machine</code>. This will help you identify this environment later.

                        <div style="margin-top: 10px;">
                            <label for="envname">Environment name:</label>
                            <input name="envname" type="text" id="envname" value="default" />

                        </div>

                    </li>
                </ul>





            </div>

        </div>

        <div style="text-align: center;">
            <?php
            if ($requirementsChecker->isWithoutCriticalErrors()) {
                $activationUrl = admin_url('admin.php?page=versionpress/admin/index.php&init_versionpress');
                $buttonClass = "button-primary";
            } else {
                $activationUrl = "#";
                $buttonClass = "button-primary-disabled";
            }
            ?>
            <a href="<?php echo esc_url($activationUrl); ?>"
               class="button <?php echo esc_attr($buttonClass); ?> button-hero" id="activate-versionpress-btn">Activate
                VersionPress</a>
        </div>

    </div>


</div>
