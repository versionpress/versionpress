This bundled library is used when the [WP-API](https://github.com/WP-API/WP-API) plugin is not installed.
Technically, it is the minimal REST API needed for VersionPress extracted from WP-API. 

The minimal code is extracted manually, when updating to newer WP-API, don't forget to:

- Remove unnecessary files:
    - non-source files
    - endpoints
    - `compatibility-v1.php`
    - `wp-api.js`
- Remove unnecessary functions and actions:
    - `register_api_field`
    - `_add_extra_api_post_type_arguments`
    - `_add_extra_api_taxonomy_arguments`
    - `create_initial_rest_routes`
    - `rest_register_scripts`
- Rename REST_API_VERSION constant to VP_REST_API_VERSION
- Change all occurences of 'rest_' to 'vp_rest_'
- Change `vp_rest_url_prefix` value to 'vp-json'
- Change <api name='WP-API'> argument in `rest_output_rsd` to 'VP-API'
- Put all classes to namespace `VersionPress\Api\BundledWpApi`
- Add use statements for all used classes beside `VersionPress\Api`
- Change `$wp_vp_rest_server_class` value to 'VersionPress\\Api\\BundledWpApi\\WP_REST_Server'
