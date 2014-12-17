<?php

class JsRedirect {


    /**
     * Echoes window.location JavaScript that redirects the browser
     *
     * @param string $url
     * @param int $timeout Delay in milliseconds
     */
    public static function redirect($url, $timeout = 0) {
        $redirectionJs = <<<JS
<script type="text/javascript">
    window.setTimeout(function () {
        window.location = '$url';
    }, $timeout);
</script>
JS;
        echo $redirectionJs;
    }
} 
