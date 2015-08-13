<?php
$apiConfig = array(
    'root' => get_site_url(),
    'urlPrefix' => vp_rest_get_url_prefix(),
    'queryParam' => 'vp_rest_route',
    'prettyPermalinks' => get_option('permalink_structure')
);
?>
<script>
    var VP_API_Config = <?php echo json_encode($apiConfig); ?>
</script>

<?php
wp_enqueue_style('versionpress_gui_style', plugins_url('../public/gui/app.css', __FILE__));
wp_enqueue_script('versionpress_gui_script', plugins_url('../public/gui/app.js', __FILE__));
