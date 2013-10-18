<?php
if(isset($_GET['sync'])) {
    Git::pull();
    require_once __DIR__ . '/../../versionpress/sync.php';
}
?>
<h1>VersionPress</h1>
<form method="POST" action="<?php echo admin_url('admin.php?page=versionpress/test.php&sync'); ?>">
<input type="submit" value="Synchronize">
</form>