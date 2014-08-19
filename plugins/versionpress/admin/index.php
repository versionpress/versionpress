<?php

wp_enqueue_style('versionpress_admin_style', plugins_url( 'style.css' , __FILE__ ));

/**
 * Function executed from Initializer that is given the progress message, decides
 * whether it is suitable for output and if so, calls `show_message()` (WP function).
 *
 * @param string $progressMessage
 */
function _vp_show_progress_message($progressMessage) {

    // We currently only output messages that are defined in InitializerStates
    // which captures the main progress points without too many details

    $initializerStatesReflection = new ReflectionClass('InitializerStates');
    $progressConstantValues = array_values($initializerStatesReflection->getConstants());
    if (in_array($progressMessage, $progressConstantValues)) {
        show_message($progressMessage);
    }
}

?>

<style>
    /* Activation-specific styles need to be defined this way because wp_enqueue_style() puts the style in the footer
       and in some cases, e.g. during the long-running initialization, the footer is not reached until
       all the messages are drawn to the page. This might be improved in the future. */

    .vp-index .welcome-panel {
        padding-bottom: 23px;
        margin-top: 40px;
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


<div class="wrap vp-index">

    <?php

    if (isset($_GET['init']) && !vp_is_active()) {

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


                    ?>

                </div>

                <p class="initialization-done">All done, we're now redirecting you (or <a href="<?php admin_url('admin.php?page=versionpress/admin/index.php') ?>">click here</a>).</p>
                <?php
                JsRedirect::redirect(admin_url('admin.php?page=versionpress/admin/index.php'), 1000);
                ?>

            </div>


        </div>

        <?php


    } elseif (!vp_is_active()) {

        wp_enqueue_style('versionpress_admin_deactivation_icons', plugins_url( 'icons/style.css' , __FILE__ ));
    ?>

        <div class="welcome-panel">

            <div class="welcome-panel-content">

                <h3>Welcome to VersionPress!</h3>

                <p class="about-description">We're ready to make your admin experience better. Click the button below so that VersionPress can initialize its internal repository and start tracking your site. A few things to know:</p>

                <ul>

                    <li>
                        <span class="icon icon-checkmark"></span>
                        VersionPress doesn't require any interaction or further setup. Just activate it and it will start doing its thing.
                    </li>

                    <li>
                        <span class="icon icon-checkmark"></span>
                        To undo a change or roll back to some previous state of the site, use the VersionPress menu item on the left.
                    </li>

                    <li>
                        <span class="icon icon-checkmark"></span>
                        VersionPress never sends your data anywhere – it is not a service, it is an installed thing on your server.
                    </li>

                    <li>
                        <span class="icon icon-notification"></span>
                        You are using a preview version of VersionPress which is only suitable for testing purposes. If you encounter any issues you can always contact us at info@versionpress.net.
                    </li>


                </ul>

                <div style="text-align: center;">
                    <a href="<?php echo admin_url('admin.php?page=versionpress/admin/index.php&init'); ?>" class="button button-primary button-hero" id="activate-versionpress-btn">Activate VersionPress</a>
                </div>

            </div>


        </div>

    <?php
    } else {
        global $versionPressContainer;

        if (isset($_GET['revert'])) {
            /** @var Reverter $reverter */
            $reverter = $versionPressContainer->resolve(VersionPressServices::REVERTER);
            $revertSuccessful = $reverter->revert($_GET['revert']);

            if(!$revertSuccessful) {
                echo "<div class='error'>Error: Overwritten changes can not be reverted.</div>";
            }
        }
        if (isset($_GET['revert-all'])) {
            /** @var Reverter $reverter */
            $reverter = $versionPressContainer->resolve(VersionPressServices::REVERTER);
            $reverter->revertAll($_GET['revert-all']);
        }
    ?>

        <h2>VersionPress</h2>

        <table id="versionpress-commits-table" class="wp-list-table widefat fixed posts">
            <tr>
                <th class="manage-column column-date">Date</th>
                <th class="manage-column column-message">Message</th>
                <th class="manage-column column-actions"></th>
            </tr>
            <tbody id="the-list">
            <?php

            /**
             * @param Commit $commit
             * @return ChangeInfo
             */
            function createChangeInfo(Commit $commit) {
                /** @var ChangeInfo[] $changeInfoClasses */
                $changeInfoClasses = array(
                    'PluginChangeInfo',
                    'WordPressUpdateChangeInfo',
                    'VersionPressChangeInfo',
                    'RevertChangeInfo',
                    'PostChangeInfo',
                    'CommentChangeInfo',
                    'OptionChangeInfo',
                    'TermChangeInfo',
                    'UserChangeInfo',
                    'CustomChangeInfo',
                );
                $matchingChangeInfoClass = 'CustomChangeInfo'; // fallback
                foreach ($changeInfoClasses as $changeInfoClass) {
                    if ($changeInfoClass::matchesCommitMessage($commit->getMessage())) {
                        $matchingChangeInfoClass = $changeInfoClass;
                        break;
                    }
                }
                $changeInfo = $matchingChangeInfoClass::buildFromCommitMessage($commit->getMessage());
                return $changeInfo;
            }

            $commits = Git::log();
            $isFirstCommit = true;

            foreach ($commits as $commit) {
                $changeInfo = createChangeInfo($commit);
                $revertAllSnippet = $isFirstCommit ? "" : "|
                    <a href='" . admin_url('admin.php?page=versionpress/admin/index.php&revert-all=' . $commit->getHash()) . "' style='text-decoration:none; white-space:nowrap;' title='Reverts site back to this state; effectively undos all the change up to this commit'>
                    Revert to this
                </a>";

                $message = $changeInfo->getChangeDescription();
                echo "
            <tr class=\"post-1 type-post status-publish format-standard hentry category-uncategorized alternate level-0\">
                <td>{$commit->getRelativeDate()}</td>
                <td>$message</td>
                <td style=\"text-align: right\">
                    <a href='" . admin_url('admin.php?page=versionpress/admin/index.php&revert=' . $commit->getHash()) . "' style='text-decoration:none; white-space:nowrap;' title='Reverts changes done by this commit'>
                    Undo this
                    </a>
                    $revertAllSnippet
                </td>
            </tr>";

                $isFirstCommit = false;
            }
            ?>
            </tbody>
        </table>
    <?php
    }
    ?>

</div>