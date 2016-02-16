<?php

if ( ! function_exists( 'json_last_error_msg' ) ) :
    /**
     * Retrieves the error string of the last json_encode() or json_decode() call.
     *
     * @since 4.4.0
     *
     * @internal This is a compatibility function for PHP <5.5
     *
     * @return bool|string Returns the error message on success, "No Error" if no error has occurred,
     *                     or false on failure.
     */
    function json_last_error_msg() {
        // See https://core.trac.wordpress.org/ticket/27799.
        if ( ! function_exists( 'json_last_error' ) ) {
            return false;
        }
        $last_error_code = json_last_error();
        // Just in case JSON_ERROR_NONE is not defined.
        $error_code_none = defined( 'JSON_ERROR_NONE' ) ? JSON_ERROR_NONE : 0;
        switch ( true ) {
            case $last_error_code === $error_code_none:
                return 'No error';
            case defined( 'JSON_ERROR_DEPTH' ) && JSON_ERROR_DEPTH === $last_error_code:
                return 'Maximum stack depth exceeded';
            case defined( 'JSON_ERROR_STATE_MISMATCH' ) && JSON_ERROR_STATE_MISMATCH === $last_error_code:
                return 'State mismatch (invalid or malformed JSON)';
            case defined( 'JSON_ERROR_CTRL_CHAR' ) && JSON_ERROR_CTRL_CHAR === $last_error_code:
                return 'Control character error, possibly incorrectly encoded';
            case defined( 'JSON_ERROR_SYNTAX' ) && JSON_ERROR_SYNTAX === $last_error_code:
                return 'Syntax error';
            case defined( 'JSON_ERROR_UTF8' ) && JSON_ERROR_UTF8 === $last_error_code:
                return 'Malformed UTF-8 characters, possibly incorrectly encoded';
            case defined( 'JSON_ERROR_RECURSION' ) && JSON_ERROR_RECURSION === $last_error_code:
                return 'Recursion detected';
            case defined( 'JSON_ERROR_INF_OR_NAN' ) && JSON_ERROR_INF_OR_NAN === $last_error_code:
                return 'Inf and NaN cannot be JSON encoded';
            case defined( 'JSON_ERROR_UNSUPPORTED_TYPE' ) && JSON_ERROR_UNSUPPORTED_TYPE === $last_error_code:
                return 'Type is not supported';
            default:
                return 'An unknown error occurred';
        }
    }
endif;

if ( ! interface_exists( 'JsonSerializable' ) ) {
    define( 'WP_JSON_SERIALIZE_COMPATIBLE', true );
    /**
     * JsonSerializable interface.
     *
     * Compatibility shim for PHP <5.4
     *
     * @link http://php.net/jsonserializable
     *
     * @since 4.4.0
     */
    interface JsonSerializable {
        public function jsonSerialize();
    }
}
