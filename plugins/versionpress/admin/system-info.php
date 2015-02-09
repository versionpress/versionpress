<?php

use Utils\SystemInfo;
use VersionPress\Utils\RequirementsChecker;

defined('ABSPATH') or die("Direct access not allowed");

?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/8.4/styles/default.min.css"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/8.4/highlight.min.js"></script>
<script>
    hljs.configure({languages: []}); // disable automatic language detection
    hljs.initHighlightingOnLoad();
</script>

<h1>System info</h1>

<h2>Git</h2>

<pre><code style="language-php">
<?php var_export(SystemInfo::getGitInfo()); ?>
</code></pre>


<h2>WordPress</h2>

<pre><code style="language-php">
<?php var_export(SystemInfo::getWordPressInfo()); ?>
</code></pre>



<h2>Server environment</h2>

<pre><code style="language-php">
<?php var_export(SystemInfo::getPhpInfo()); ?>
</code></pre>

