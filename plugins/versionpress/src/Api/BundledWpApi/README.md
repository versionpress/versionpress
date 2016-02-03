This bundled library is used when the the WP-API is not in WordPress core (until WP 4.4)

The code is updated manually, when updating to newer version, don't forget to:

- Put all global functions and classes into conditions to avoid redeclaration
- Copy function `wp_json_encode` that has been redeclared in WP 4.4 and rename it to `wp_vp_json_encode`
- Change all uses of `wp_json_encode` in BundledWpApi to `wp_vp_json_encode`
- Add function `vp_rest_api_maybe_flush_rewrites` hooked to 'init' after `rest_api_init`