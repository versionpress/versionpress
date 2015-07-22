<?php

namespace VersionPress\Initialization;

use Nette\Utils\Strings;

class WpdbReplacer {

    private static $methodPrefix = '__wp_';

    private static $insertMethod = '    public function insert( $table, $data, $format = null ) {
        $r = $this->__wp_insert($table, $data, $format);

        $this->vp_backup_fields();

		/**
		 * Fires after the insert query was executed.
		 *
		 * @since VersionPress
		 */
		do_action( "wpdb_after_insert", $table, $data );

		$this->vp_restore_fields();

        return $r;
    }

';
    private static $updateMethod = '    public function update( $table, $data, $where, $format = null, $where_format = null ) {
        $r = $this->__wp_update($table, $data, $where, $format, $where_format);

        $this->vp_backup_fields();

		/**
		 * Fires after the update query was executed.
		 *
		 * @since VersionPress
		 */
		do_action( "wpdb_after_update", $table, $data, $where );

		$this->vp_restore_fields();

		return $r;
    }

';
    private static $deleteMethod = '    public function delete( $table, $where, $where_format = null ) {
        $r = $this->__wp_delete($table, $where, $where_format);

        $this->vp_backup_fields();

		/**
		 * Fires after the delete query was executed.
		 *
		 * @since VersionPress
		 */
		do_action( "wpdb_after_delete", $table, $where);

		$this->vp_restore_fields();

		return $r;
    }

';

    private static $backupMethods = '	/**
	 * Used by VersionPress for restoring last_query, last_error etc. after its hook.
	 *
	 * @since VersionPress
	 * @var array
	 */
	private $vp_field_backup = array();

    /**
	 * @since VersionPress
	 */
	private function vp_backup_fields() {
		$this->vp_field_backup = array(
			"last_error" => $this->last_error,
			"last_query" => $this->last_query,
			"last_result" => $this->last_result,
			"rows_affected" => $this->rows_affected,
			"num_rows" => $this->num_rows,
			"insert_id" => $this->insert_id,
		);
	}

	/**
	 * @since VersionPress
	 */
	private function vp_restore_fields() {
		$this->last_error = $this->vp_field_backup["last_error"];
		$this->last_query = $this->vp_field_backup["last_query"];
		$this->last_result = $this->vp_field_backup["last_result"];
		$this->rows_affected = $this->vp_field_backup["rows_affected"];
		$this->num_rows = $this->vp_field_backup["num_rows"];
		$this->insert_id = $this->vp_field_backup["insert_id"];
	}
';
    private static $vpFirstLineComment = '// Enhanced by VersionPress';

    public static function replaceMethods() {
        $wpdbClassPath = ABSPATH . WPINC . '/wp-db.php';
        $wpdbSource = file_get_contents($wpdbClassPath);

        if (self::isReplaced()) {
            return;
        }

        copy($wpdbClassPath, $wpdbClassPath . '.original');

        $wpdbSource = substr_replace($wpdbSource, '<?php ' . self::$vpFirstLineComment, 0, strlen('<?php')); // adds the VP comment
        $wpdbSource = self::replaceMethod($wpdbSource, 'insert');
        $wpdbSource = self::replaceMethod($wpdbSource, 'update');
        $wpdbSource = self::replaceMethod($wpdbSource, 'delete');
        $wpdbSource = self::injectVersionPressMethods($wpdbSource);

        file_put_contents($wpdbClassPath, $wpdbSource);
    }

    public static function isReplaced() {
        $firstLine = fgets(fopen(ABSPATH . WPINC . '/wp-db.php', 'r'));
        return Strings::contains($firstLine, self::$vpFirstLineComment);
    }

    public static function restoreOriginal() {
        rename(ABSPATH . WPINC . '/wp-db.php.original', ABSPATH . WPINC . '/wp-db.php');
    }

    private static function replaceMethod($source, $method) {
        $newName = self::$methodPrefix . $method;
        return str_replace("function $method(", "function $newName(", $source);
    }

    private static function injectVersionPressMethods($wpdbSource) {
        $indexOfLastCurlyBracket = strrpos($wpdbSource, '}');
        $wpdbSource = self::injectCode($wpdbSource, $indexOfLastCurlyBracket, self::$deleteMethod);
        $wpdbSource = self::injectCode($wpdbSource, $indexOfLastCurlyBracket, self::$updateMethod);
        $wpdbSource = self::injectCode($wpdbSource, $indexOfLastCurlyBracket, self::$insertMethod);
        $wpdbSource = self::injectCode($wpdbSource, $indexOfLastCurlyBracket, self::$backupMethods);

        return $wpdbSource;
    }

    private static function injectCode($originalSource, $position, $code) {
        return substr($originalSource, 0, $position) . $code . substr($originalSource, $position);
    }
}