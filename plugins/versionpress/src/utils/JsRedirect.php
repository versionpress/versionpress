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
    public static function redirect($url) {
        $redirectionJs = <<<JS
<script type="text/javascript">
    window.location = '$url';
</script>
JS;
        echo $redirectionJs;
    }
} 