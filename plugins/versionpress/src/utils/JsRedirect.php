<?php
/**
 * Created by IntelliJ IDEA.
 * User: Borek
 * Date: 14. 8. 2014
 * Time: 16:00
 */

class JsRedirect {


    /**
     * Echoes window.location JavaScript that redirects the browser
     *
     * @param string $url
     */
    public static function redirect($url, $timeout = 0) {
        $redirectionJs = <<<JS
<script type="text/javascript">
    window.setTimeout(function() {
        window.location = '$url';
    }, $timeout);
</script>
JS;
        echo $redirectionJs;
    }
} 