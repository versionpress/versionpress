# UI Not Loading

If VersionPress admin screens aren't loading, there's probably some misconfiguration that prevents JavaScript to communicate with REST API on the server. It will be stuck at something like this:

![image](https://cloud.githubusercontent.com/assets/101152/15138817/9f21ef1e-1692-11e6-90a0-9bb4737abb0f.png)

To troubleshoot it:

**1)** In your browser, do a request to `http://yoursite/wp-json/` (or `http://yoursite/?rest_route=/` if you don't have permalinks enabled; see [REST API Discovery](http://v2.wp-api.org/guide/discovery/)).

You should see VersionPress mentioned there. If not, try to change permalink settings to something else.

**2)** In the browser dev tools, go to the _Network_ tab and see if the request to REST API endpoints like `commits` return 2xx OK values. If not, make note whether it's 403 (Forbidden), 404 (Not Found) or something else and open an issue in the [support repo](https://github.com/versionpress/support).

**3)** If all the above works, try to collect all the useful console output and open an issue in the [support repo](https://github.com/versionpress/support).
