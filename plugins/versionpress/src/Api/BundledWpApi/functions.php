<?php

if (!function_exists('wp_is_numeric_array')) {
    /**
     * Determines if the variable is a numeric-indexed array.
     *
     * @since 4.4.0
     *
     * @param mixed $data Variable to check.
     * @return bool Whether the variable is a list.
     */
    function wp_is_numeric_array( $data ) {
        if ( ! is_array( $data ) ) {
            return false;
        }
        $keys = array_keys( $data );
        $string_keys = array_filter( $keys, 'is_string' );
        return count( $string_keys ) === 0;
    }
}

/**
 * Encode a variable into JSON, with some sanity checks.
 *
 * @since 4.1.0
 *
 * @param mixed $data    Variable (usually an array or object) to encode as JSON.
 * @param int   $options Optional. Options to be passed to json_encode(). Default 0.
 * @param int   $depth   Optional. Maximum depth to walk through $data. Must be
 *                       greater than 0. Default 512.
 * @return string|false The JSON encoded string, or false if it cannot be encoded.
 */
function wp_vp_json_encode( $data, $options = 0, $depth = 512 ) {
    /*
     * json_encode() has had extra params added over the years.
     * $options was added in 5.3, and $depth in 5.5.
     * We need to make sure we call it with the correct arguments.
     */
    if ( version_compare( PHP_VERSION, '5.5', '>=' ) ) {
        $args = array( $data, $options, $depth );
    } elseif ( version_compare( PHP_VERSION, '5.3', '>=' ) ) {
        $args = array( $data, $options );
    } else {
        $args = array( $data );
    }
    // Prepare the data for JSON serialization.
    $data = _wp_json_prepare_data( $data );
    $json = @call_user_func_array( 'json_encode', $args );
    // If json_encode() was successful, no need to do more sanity checking.
    // ... unless we're in an old version of PHP, and json_encode() returned
    // a string containing 'null'. Then we need to do more sanity checking.
    if ( false !== $json && ( version_compare( PHP_VERSION, '5.5', '>=' ) || false === strpos( $json, 'null' ) ) )  {
        return $json;
    }
    try {
        $args[0] = _wp_json_sanity_check( $data, $depth );
    } catch ( Exception $e ) {
        return false;
    }
    return call_user_func_array( 'json_encode', $args );
}

if (!function_exists('_wp_json_prepare_data')) {
    /**
     * Prepares response data to be serialized to JSON.
     *
     * This supports the JsonSerializable interface for PHP 5.2-5.3 as well.
     *
     * @ignore
     * @since 4.4.0
     * @access private
     *
     * @param mixed $data Native representation.
     * @return bool|int|float|null|string|array Data ready for `json_encode()`.
     */
    function _wp_json_prepare_data( $data ) {
        if ( ! defined( 'WP_JSON_SERIALIZE_COMPATIBLE' ) || WP_JSON_SERIALIZE_COMPATIBLE === false ) {
            return $data;
        }
        switch ( gettype( $data ) ) {
            case 'boolean':
            case 'integer':
            case 'double':
            case 'string':
            case 'NULL':
                // These values can be passed through.
                return $data;
            case 'array':
                // Arrays must be mapped in case they also return objects.
                return array_map( '_wp_json_prepare_data', $data );
            case 'object':
                // If this is an incomplete object (__PHP_Incomplete_Class), bail.
                if ( ! is_object( $data ) ) {
                    return null;
                }
                if ( $data instanceof JsonSerializable ) {
                    $data = $data->jsonSerialize();
                } else {
                    $data = get_object_vars( $data );
                }
                // Now, pass the array (or whatever was returned from jsonSerialize through).
                return _wp_json_prepare_data( $data );
            default:
                return null;
        }
    }
}

if (!function_exists('mysql_to_rfc3339')) {
    /**
     * Parses and formats a MySQL datetime (Y-m-d H:i:s) for ISO8601/RFC3339.
     *
     * Explicitly strips timezones, as datetimes are not saved with any timezone
     * information. Including any information on the offset could be misleading.
     *
     * @since 4.4.0
     *
     * @param string $date_string Date string to parse and format.
     * @return string Date formatted for ISO8601/RFC3339.
     */
    function mysql_to_rfc3339( $date_string ) {
        $formatted = mysql2date( 'c', $date_string, false );
        // Strip timezone information
        return preg_replace( '/(?:Z|[+-]\d{2}(?::\d{2})?)$/', '', $formatted );
    }
}
