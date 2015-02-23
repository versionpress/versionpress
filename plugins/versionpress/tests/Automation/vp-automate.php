<?php

/**
 * Interface to WpAutomation methods and other helper commands. For example, `wp vp-automate start-over`
 * may be used to speed up testing of initialization.
 *
 * Example of usage:
 *
 *     wp --require="wp-content/plugins/versionpress/tests/Automation/vp-automate.php" vp-automate start-over
 *
 */
namespace VersionPress\Tests\Automation;

use DateTime;
use Faker;
use mysqli;
use Nette\Utils\Random;
use Tracy\Debugger;
use VersionPress\Utils\FileSystem;
use WP_CLI;
use wpdb;

/**
 * Internal VersionPress automation commands. Some of them depend on tests-config.ini.
 */
class VpAutomateCommand extends \WP_CLI_Command {

    /** @var Faker\Generator */
    private $faker;

    /** @var wpdb */
    private $database;

    /**
     * Removes everything created by VP, leaves site fresh for new testing.
     *
     * ## DETAILS
     *
     * Basically does plugin deactivation, removing the Git repo and plugin activation.
     * Deactivation does things like removing `vpdb`, `db.php`, VersionPress db tables etc.
     *
     * @subcommand start-over
     */
    public function startOver($args, $assoc_args) {
        vp_admin_post_confirm_deactivation();
        FileSystem::remove(ABSPATH . '.git');
        activate_plugin('versionpress/versionpress.php');
    }

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

        foreach ($preferedOrder as $entity) {
            if (!isset($assoc_args[$entity])) {
                continue;
            }

            $this->generateEntities($entity, $assoc_args[$entity]);
        }
    }

    private function generateEntities($entity, $count) {
        $entities = array();
        Debugger::timer();
        for ($i = 0; $i < $count; $i++) {
            $entities[] = $this->generateEntity($entity);
        }

        WP_CLI::success("Generating ($entity): " . Debugger::timer());
        $insertQueries = $this->buildInsertQueries($this->database->prefix . $entity, $entities);
        WP_CLI::success("Building queries ($entity): " . Debugger::timer());

        $connection = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $connection->query("SET GLOBAL max_allowed_packet=100*1024*1024");
        $chunks = array_chunk($insertQueries, 50);
        foreach ($chunks as $chunk) {
            $connection->multi_query(join(" ", $chunk));
            while ($connection->next_result()) // flush multi_queries
            {
                if (!$connection->more_results()) break;
            }
        }

        WP_CLI::success("Queries ($entity): " . Debugger::timer());

        $connection->close();
    }

    private function generateEntity($entity) {
        switch ($entity) {
            case 'options':
                return $this->generateOption();
            case 'users':
                return $this->generateUser();
            case 'terms':
                return $this->generateTerm();
            case 'posts':
                return $this->generatePost();
            case 'comments':
                return $this->generateComment();
        }
        return null;
    }

    private function generateOption() {
        $optionName = "vp_random_" . Random::generate(10, 'a-z');
        $optionValue = $this->faker->words(rand(1, 20));
        add_option($optionName, $optionValue);
        return array();
    }

    private function generateUser() {
        $firstName = $this->faker->firstName;
        $lastName = $this->faker->lastName;
        $fullName = $firstName . ' ' . $lastName;
        $userName = $this->faker->userName;

        $userdata = array(
            'user_pass' => 'password',
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
        return array();
    }

    private function generateTerm() {
        $taxonomies = array('post_tag', 'category');

        $name = $this->faker->word;
        $randomTaxonomy = self::randomEntry($taxonomies);
        wp_insert_term($name, $randomTaxonomy);
        return array();
    }

    private function generatePost() {
        static $authors;

        if (!$authors) {
            $authors = array_map($this->getFieldFn('ID'), get_users());
        }

        $contentLength = rand(50, 1000); // characters

        $date = new DateTime();
        $post = $this->preparePost(array(
            "post_type" => "post",
            "post_status" => "publish",
            "post_title" => $this->generateLoremIpsum(1, false),
            "post_date" => $date->format('Y-m-d H:i:s'),
            "post_content" => $this->generateLoremIpsum($contentLength),
            "post_author" => self::randomEntry($authors)
        ));

        return $post;

    }

    private function generateComment() {
        static $posts;
        static $authors;

        if (!$posts) {
            $posts = array_map($this->getFieldFn('ID'), get_posts());
            $authors = array_map($this->getFieldFn('ID'), get_users());
        }

        $commentLength = rand(2, 50);
        $hasUserId = rand(0, 10) < 7;

        $comment = $this->prepareComment(array(
            'comment_author' => $this->faker->name,
            'comment_author_email' => $this->faker->email,
            'comment_author_url' => $this->faker->url,
            'comment_date' => $this->faker->dateTime->format('Y-m-d H:i:s'),
            'comment_content' => $this->generateLoremIpsum($commentLength),
            'comment_approved' => 1,
            'comment_post_ID' => self::randomEntry($posts),
            'user_id' => $hasUserId ? self::randomEntry($authors) : 0
        ));

        return $comment;
    }

    private static function randomEntry(array $array) {
        return $array[array_rand($array, 1)];
    }

    private static function randomEntries(array $array, $count) {
        $randomKeys = (array)array_rand($array, $count);
        return array_map(function ($key) use ($array) {
            return $array[$key];
        }, $randomKeys);
    }

    private function getFieldFn($field) {
        return function ($user) use ($field) {
            return $user->{$field};
        };
    }

    private function preparePost($post) {
        $defaults = array(
            'post_date_gmt' => $post['post_date'],
            'post_name' => $this->faker->slug,
            'post_modified' => $post['post_date'],
            'post_modified_gmt' => $post['post_date'],
            'guid' => $this->faker->uuid,
        );

        return array_merge($defaults, $post);
    }

    private function prepareComment($comment) {
        $defaults = array(
            'comment_date_gmt' => $comment['comment_date'],
        );

        return array_merge($defaults, $comment);
    }

    private function buildInsertQueries($table, $entities) {
        if (count($entities) == 0) {
            return "";
        }

        $columns = "`" . join("`, `", array_keys($entities[0])) . "`";

        $valueStrings = array_map(function ($entity) {
            return "(" . join(", ", array_map(function ($value) {
                return is_string($value) ? "\"" . mysql_real_escape_string($value) . "\"" : $value;
            }, $entity)) . ")";
        }, $entities);


        return array_map(function ($valuesString) use ($table, $columns) {
            return "INSERT INTO $table ($columns) VALUES $valuesString;";
        }, $valueStrings);
    }

    private function generateLoremIpsum($countOfSentences, $period = true) {
        static $lipsum;
        if (!$lipsum) {
            $lipsum = array(
                'lorem', 'ipsum', 'dolor', 'sit', 'amet', 'consectetur',
                'adipiscing', 'elit', 'curabitur', 'vel', 'hendrerit', 'libero',
                'eleifend', 'blandit', 'nunc', 'ornare', 'odio', 'ut',
                'orci', 'gravida', 'imperdiet', 'nullam', 'purus', 'lacinia',
                'a', 'pretium', 'quis', 'congue', 'praesent', 'sagittis');
        }

        $sentences = array();
        for ($i = 0; $i < $countOfSentences; $i++) {
            $sentenceLength = rand(5, 20);
            $randomWords = array_intersect_key($lipsum, array_flip(array_rand($lipsum, $sentenceLength)));

            $sentences[] = ucfirst(join(" ", $randomWords)) . ($period ? "." : "");
        }

        return join(" ", $sentences);
    }

}

if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('vp-automate', 'VersionPress\Tests\Automation\VpAutomateCommand');
}