<?php

namespace VersionPress\Tests\SynchronizerTests\Utils;

use Nette\Utils\Random;
use VersionPress\Utils\IdUtil;

class EntityUtils {

    public static function prepareOption($name, $value) {
        return array('option_name' => $name, 'option_value' => $value, 'autoload' => 'yes');
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
            'post_modified' => '0000-00-00 00:00:00',
            'post_modified_gmt' => '0000-00-00 00:00:00',
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
            'vp_post_parent' => 0,
            'vp_post_author' => 0,
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

    public static function preparePostMeta($vpId = null, $postVpId = null, $key = null, $value = null) {
        if ($vpId === null) {
            $vpId = IdUtil::newId();
        }

        $postmeta = array(
            'vp_id' => $vpId
        );

        if ($postVpId !== null) {
            $postmeta['vp_post_id'] = $postVpId;
        }

        if ($key !== null) {
            $postmeta['meta_key'] = $key;
        }

        if ($value !== null) {
            $postmeta['meta_value'] = $value;
        }

        return $postmeta;
    }

    public static function prepareComment($vpId = null, $postVpId = null, $authorVpId = null, $commentValues = array()) {
        if ($vpId === null) {
            $vpId = IdUtil::newId();
        }

        $comment = array_merge(array(
            'comment_author' => 'Joe Tester',
            'comment_author_email' => 'joetester@example.com',
            'comment_author_url' => '',
            'comment_date' => '2012-12-12 12:12:12',
            'comment_date_gmt' => '0000-00-00 00:00:00',
            'comment_content' => 'Some content',
            'comment_approved' => 1,
            'comment_author_IP' => '',
            'comment_karma' => 0,
            'comment_agent' => '',
            'comment_type' => '',
            'comment_parent' => 0,
            'vp_comment_parent' => 0,
            'vp_id' => $vpId,
        ), $commentValues);

        if ($postVpId !== null) {
            $comment['vp_comment_post_ID'] = $postVpId;
        }

        if ($authorVpId !== null) {
            $comment['vp_user_id'] = $authorVpId;
        }

        return $comment;
    }

    public static function prepareTerm($vpId = null, $name = null, $slug = null) {
        if ($vpId === null) {
            $vpId = IdUtil::newId();
        }

        $term = array(
            'vp_id' => $vpId,
            'name' => 'Some term',
            'slug' => 'some-term',
            'term_group' => 0
        );

        if (isset($name)) {
            $term['name'] = $name;
        }

        if (isset($slug)) {
            $term['slug'] = $slug;
        }

        return $term;
    }

    public static function prepareTermTaxonomy($vpId = null, $termVpId = null, $taxonomy = null, $description = null) {
        if ($vpId === null) {
            $vpId = IdUtil::newId();
        }

        $termTaxonomy = array(
            'vp_id' => $vpId
        );

        if ($termVpId !== null) {
            $termTaxonomy['vp_term_id'] = $termVpId;
        }

        if ($taxonomy !== null) {
            $termTaxonomy['taxonomy'] = $taxonomy;
        }

        if ($description !== null) {
            $termTaxonomy['description'] = $description;
        }

        $termTaxonomy['vp_parent'] = 0;

        return $termTaxonomy;

    }

    public static function prepareTermMeta($vpId = null, $termVpId = null, $key = null, $value = null) {
        if ($vpId === null) {
            $vpId = IdUtil::newId();
        }

        $termmeta = array(
            'vp_id' => $vpId
        );

        if ($termVpId !== null) {
            $termmeta['vp_term_id'] = $termVpId;
        }

        if ($key !== null) {
            $termmeta['meta_key'] = $key;
        }

        if ($value !== null) {
            $termmeta['meta_value'] = $value;
        }

        return $termmeta;
    }
}
