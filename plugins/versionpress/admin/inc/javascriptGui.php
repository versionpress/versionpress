<?php
$apiConfig = array(
    'root' => get_site_url(),
    'adminUrl' => get_admin_url(),
    'urlPrefix' => rest_get_url_prefix(),
    'queryParam' => 'rest_route',
    'permalinkStructure' => get_option('permalink_structure'),
    'nonce' => wp_create_nonce('wp_rest')
);
?>
<script>
    var VP_API_Config = <?php echo json_encode($apiConfig); ?>
</script>

<?php
wp_enqueue_style('versionpress_gui_style', plugins_url('../public/gui/app.css', __FILE__));
wp_enqueue_script('versionpress_gui_script', plugins_url('../public/gui/app.js', __FILE__));
