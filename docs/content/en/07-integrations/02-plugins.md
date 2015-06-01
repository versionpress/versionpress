# Plugins #

This page lists supported, unsupported and partially supported plugins. For a general overview of how 3rd party plugins are handled, please see the [integrations](.) page.


## General notes

There are three general categories of plugins when it comes to VersionPress: 

 1. **Plugins that work just fine**. This is when the db operations are relatively simple, for example, updating some site option will be captured and understood by VersionPress fine.
 2. **Plugins that work, technically, but the messages are not very helpful**. For example, if an e-commerce solution uses some custom post types and actions, you will see something like *"Updated post of type 'product'"* while the actual action could be much better described as *"Purchased product xyz"*.
 3. **The plugin isn't supported at all**. This can happen for example when the plugin uses its own database table or does non-standard things.



## Supported plugins

Plugins that are known to work fine, or very close to that, are:

 - **All plugins that don't manipulate the database**. Many plugins fall into this category, from scripts for adding Google Analytics to a page to various filters and smaller helpers. 
 - Hello Dolly :-)
 - Akismet
 - ACF


## Unsupported plugins 

This (incomplete!) section lists plugins that are known to cause issues.

### Caching plugins (W3 Total Cache and others)

VersionPress currently uses the `wp-content/db.php` file to hook into some WordPress actions for the lack of better extensibility points (see [WP issue #29710](https://core.trac.wordpress.org/ticket/29710) and [this suggestion](https://wordpress.org/ideas/topic/multiple-dbphp-files-for-plugins)). This means that VersionPress will conflict with other plugins that want to use db.php which is the case of W3 Total Cache and some other caching plugins. We will work with the WordPress community to resolve this.


## Partially supported plugins

### Jetpack

Jetpack is actually a collection of plugins, most of which work fine. Some sub-plugins are of category 2 above, i.e., the actions are technically tracked but the descriptions could be improved.

<div class="warning">
  <strong>Jetpack 3.4 and security reports</strong>
  <p>JetPack 3.4 introduced a new <a href="http://jetpack.me/2015/03/17/jetpack-3-4-protect-secure-and-simplify/">security feature</a> that sends report every 15 minutes and writes the timestamp in the database, **even if the "Protect" module is disabled**. This means that VersionPress will create a meaningless commit every 15 minutes which is undesirable. The current solution is to manually update the code in <code><a href="http://jetpack.wp-a2z.org/oik_api/jetpackperform_security_reporting/">Jetpack::perform_security_reporting()</a></code> to either disable the functionality or to prolong the period, e.g. to <code>1 * DAY_IN_SECONDS</code>. We will have a fix in a future VersionPress update.</p>
</div>
