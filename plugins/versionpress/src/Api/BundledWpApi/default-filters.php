<?php
// @codingStandardsIgnoreFile

// REST API filters.
add_action( 'xmlrpc_rsd_apis',            'rest_output_rsd' );
add_action( 'wp_head',                    'rest_output_link_wp_head', 10, 0 );
add_action( 'template_redirect',          'rest_output_link_header', 11, 0 );
add_action( 'auth_cookie_malformed',      'rest_cookie_collect_status' );
add_action( 'auth_cookie_expired',        'rest_cookie_collect_status' );
add_action( 'auth_cookie_bad_username',   'rest_cookie_collect_status' );
add_action( 'auth_cookie_bad_hash',       'rest_cookie_collect_status' );
add_action( 'auth_cookie_valid',          'rest_cookie_collect_status' );
add_filter( 'rest_authentication_errors', 'rest_cookie_check_errors', 100 );

add_action( 'init',          'rest_api_init' );
add_action( 'rest_api_init', 'rest_api_default_filters', 10, 1 );
add_action( 'parse_request', 'rest_api_loaded' );

add_action( 'init',          'vp_rest_api_maybe_flush_rewrites' , 999);
