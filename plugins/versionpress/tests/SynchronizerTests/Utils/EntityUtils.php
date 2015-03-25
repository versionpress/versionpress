<?php

namespace VersionPress\Tests\SynchronizerTests\Utils;

use Nette\Utils\Random;
use VersionPress\Utils\IdUtil;

class EntityUtils {

    public static function prepareOption($name, $value) {
        return array('option_name' => $name, 'option_value' => $value);
    }

    public static function prepareUser($vpId = null, $userValues = array()) {
        if ($vpId === null) {
            $vpId = IdUtil::newId();
        }

        return array_merge(array(
            "user_login" => "JoeTester",
            "user_pass" => '$P$B3hfEaUjEIkzHqzDHQ5kCALiUGv3rt1',
            "user_nicename" => "JoeTester",
            "user_email" => "joetester@example.com",
            "user_url" => "",
            "user_registered" => "2015-02-02 14:19:58",
            "user_activation_key" => "",
            "user_status" => 0,
            "display_name" => "JoeTester",
            "vp_id" => $vpId,
        ), $userValues);
    }

    public static function preparePost($vpId = null, $authorVpId = null, $postValues = array()) {
        if ($vpId === null) {
            $vpId = IdUtil::newId();
        }

        $post = array_merge(array(
            'post_date' => "2015-02-02 14:19:59",
            'post_date_gmt' => "2015-02-02 14:19:59",
            'post_content' => "Welcome to WordPress. This is your first post. Edit or delete it, then start blogging!",
            'post_title' => "Hello world!",
            'post_excerpt' => "",
            'post_status' => "publish",
            'comment_status' => "open",
            'ping_status' => "open",
            'post_password' => "",
            'post_name' => "hello-world",
            'to_ping' => "",
            'pinged' => "",
            'post_content_filtered' => "",
            'guid' => "http://127.0.0.1/wordpress/?p=" . Random::generate(),
            'menu_order' => 0,
            'post_type' => "post",
            'post_mime_type' => "",
            'vp_id' => $vpId,
        ), $postValues);

        if ($authorVpId !== null) {
            $post['vp_post_author'] = $authorVpId;
        }

        return $post;
    }

    public static function prepareUserMeta($vpId = null, $userVpId = null, $key = null, $value = null) {
        if ($vpId === null) {
            $vpId = IdUtil::newId();
        }

        $usermeta = array(
            'vp_id' => $vpId
        );

        if ($userVpId !== null) {
            $usermeta['vp_user_id'] = $userVpId;
        }

        if ($key !== null) {
            $usermeta['meta_key'] = $key;
        }

        if ($value !== null) {
            $usermeta['meta_value'] = $value;
        }

        return $usermeta;
    }
}