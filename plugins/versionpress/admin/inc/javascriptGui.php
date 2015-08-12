<script>
    var apiBaseUrl = '<?php echo get_site_url(); ?>';
    var apiUrlPrefix = '<?php echo vp_rest_get_url_prefix(); ?>';
    var apiQueryParam = 'vp_rest_route';
    var apiPrettyPermalinks = <?php echo get_option('permalink_structure') ? 'true' : 'false' ?>;
</script>

<?php

wp_enqueue_style('versionpress_gui_style', plugins_url('../public/gui/app.css', __FILE__));
wp_enqueue_script('versionpress_gui_script', plugins_url('../public/gui/app.js', __FILE__));
