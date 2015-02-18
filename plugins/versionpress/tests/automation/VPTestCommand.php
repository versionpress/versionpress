<?php

/**
 * Test VersionPress commands.
 *
 * ## USAGE
 *
 *     wp --require=<path to VPTestCommand.php> vp-test ...
 *
 */
class VPTestCommand extends WP_CLI_Command {

    /** @var Faker\Generator */
    private $faker;

    /** @var wpdb */
    private $database;

    /**
     * Fills the site with random data.
     *
     * ## OPTIONS
     *
     * --posts=<count>
     * : Count of generated posts.
     *
     * --comments=<count>
     * : Count of generated comments.
     *
     * --users=<count>
     * : Count of generated users.
     *
     * --options=<count>
     * : Count of generated options.
     *
     * --terms=<count>
     * : Count of generated terms.
     *
     *
     * @synopsis [--posts=<count>] [--comments=<count>] [--users=<count>] [--options=<count>] [--terms=<count>]
     *
     * @subcommand generate
     *
     */
    public function generate($args, $assoc_args) {
        global $wpdb;
        $this->faker = Faker\Factory::create();
        $this->database = $wpdb;

        $preferedOrder = array(
            'options',
            'users',
            'terms',
            'posts',
            'comments'
        );

        $this->database->query("SET autocommit=0");
        $this->database->query("SET unique_checks=0");
        $this->database->query("SET foreign_key_checks=0");
        $this->database->query("START TRANSACTION");
        $this->database->query("BEGIN");

        foreach ($preferedOrder as $entity) {
            if (!isset($assoc_args[$entity])) {
                continue;
            }

            $this->generateEntities($entity, $assoc_args[$entity]);
        }

        $this->database->query("COMMIT");
        $this->database->query("SET unique_checks=1");
        $this->database->query("SET foreign_key_checks=1");

    }

    private function generateEntities($entity, $count) {
        for($i = 0; $i < $count; $i++) {
            $this->generateEntity($entity);
        }
    }

    private function generateEntity($entity) {
        switch($entity) {
            case 'options':
                $this->generateOption();
                break;
            case 'users':
                $this->generateUser();
                break;
            case 'terms':
                $this->generateTerm();
                break;
            case 'posts':
                $this->generatePost();
                break;
            case 'comments':
                $this->generateComment();
                break;
        }
    }

    private function generateOption() {
        $optionName = "vp_random_" . \Nette\Utils\Random::generate(10, 'a-z');
        $optionValue = $this->faker->words(rand(1, 20));
        add_option($optionName, $optionValue);
    }

    private function generateUser() {
        $firstName = $this->faker->firstName;
        $lastName = $this->faker->lastName;
        $fullName = $firstName . ' ' . $lastName;
        $userName = $this->faker->userName;

        $userdata = array(
            'user_pass' => $this->faker->word,
            'user_login' => $userName,
            'user_nicename' => $fullName,
            'user_email' => $this->faker->email,
            'display_name' => $fullName,
            'nickname' => $userName,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'date_registered' => $this->faker->dateTime->format('Y-m-d H:i:s'),
        );

        wp_insert_user($userdata);

    }

    private function generateTerm() {
        $taxonomies = array('post_tag', 'category');

        $name = $this->faker->word;
        $randomTaxonomy = self::randomEntry($taxonomies);
        wp_insert_term($name, $randomTaxonomy);
    }

    private function generatePost() {
        static $authors;
        static $categories;
        static $tags;

        if (!$authors) {
            $authors = array_map($this->getFieldFn('ID'), get_users());
            $categories = array_map($this->getFieldFn('term_taxonomy_id'), get_categories());
            $tags = array_map($this->getFieldFn('term_taxonomy_id'), get_tags());
        }

        $titleLength = rand(2, 10); // words
        $contentLength = rand(500, 10000); // characters

        $post = $this->preparePost(array(
            "post_type" => "post",
            "post_status" => "publish",
            "post_title" => join(' ', $this->faker->words($titleLength)),
            "post_date" => $this->faker->dateTime->format('Y-m-d H:i:s'),
            "post_content" => $this->faker->text($contentLength),
            "post_author" => self::randomEntry($authors)
        ));


        $this->database->insert($this->database->prefix . 'posts', $post);
        $postId = $this->database->insert_id;

        if (count($categories) > 0) {
            $maximumCategories = ceil(count($categories) / 2);
            wp_set_post_categories($postId, self::randomEntries($categories, rand(1, $maximumCategories)));
        }

        if (count($tags) > 0) {
            $maximumTags = ceil(count($tags) / 2);
            wp_set_post_tags($postId, self::randomEntries($tags, rand(1, $maximumTags)));
        }
    }

    private function generateComment() {
        static $posts;
        static $authors;

        if (!$posts) {
            $posts = array_map($this->getFieldFn('ID'), get_posts());
            $authors = array_map($this->getFieldFn('ID'), get_users());
        }

        $commentLength = rand(40, 1000);
        $hasUserId = rand(0, 10) < 7;

        $comment = array(
            'comment_author' => $this->faker->name,
            'comment_author_email' => $this->faker->email,
            'comment_author_url' => $this->faker->url,
            'comment_date' => $this->faker->dateTime->format('Y-m-d H:i:s'),
            'comment_content' => $this->faker->text($commentLength),
            'comment_approved' => 1,
            'comment_post_ID' => self::randomEntry($posts),
            'user_id' => $hasUserId ? self::randomEntry($authors) : 0
        );

        wp_insert_comment($comment);
    }

    private static function randomEntry(array $array) {
        return $array[array_rand($array, 1)];
    }

    private static function randomEntries(array $array, $count) {
        $randomKeys = (array)array_rand($array, $count);
        return array_map(function ($key) use ($array) { return $array[$key]; }, $randomKeys);
    }

    private function getFieldFn($field) {
        return function ($user) use ($field) {
            return $user->{$field};
        };
    }

    private function preparePost($post) {
        $defaults = array(
            'post_date_gmt' => $post['post_date'],
            'post_content' => '',
            'post_title' => '',
            'post_status' => 'publish',
            'comment_status' => 'open',
            'ping_status' => 'open',
            'post_name' => $this->faker->slug,
            'post_modified' => $post['post_date'],
            'post_modified_gmt' => $post['post_date'],
            'post_parent' => 0,
            'guid' => $this->faker->uuid,
            'post_type' => 'post',
        );

        return array_merge($defaults, $post);
    }
}

if (defined('WP_CLI') && WP_CLI) {
    require_once(__DIR__ . '/../../vendor/autoload.php');
    WP_CLI::add_command('vp-test', 'VPTestCommand');
}
