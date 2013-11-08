<?php
if (isset($_GET['pull'])) {
    Git::pull();
    require_once __DIR__ . '/../../versionpress/sync.php';
}
if (isset($_GET['push'])) {
    Git::pull();
    Git::push();
    file_get_contents("http://localhost/wordpress/wp-content/versionpress/sync.php");
}
?>
<h1>VersionPress</h1>
<form method="POST" action="<?php echo admin_url('admin.php?page=versionpress/test.php&pull'); ?>">
    <input type="submit" value="Synchronize - pull">
</form>
<form method="POST" action="<?php echo admin_url('admin.php?page=versionpress/test.php&push'); ?>">
    <input type="submit" value="Synchronize - push">
</form>