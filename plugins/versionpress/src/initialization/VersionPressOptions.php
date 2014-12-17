<?php

namespace VersionPress\Initialization;
/**
 * Constants listing what options VersionPress uses. Constants with the prefix `USER_META_` go into
 * the `user_meta` table, the others go to standard `options` table.
 */
class VersionPressOptions {

    /**
     * Similar to WP's show_welcome_panel. If `1`, show welcome panel above the main VersionPress table.
     */
    const USER_META_SHOW_WELCOME_PANEL = 'vp_show_welcome_panel';
}