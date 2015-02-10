# External Plugins Support

WordPress is a very rich ecosystem and its plugins can do anything and everything. This is a huge advantage but also a big challenge for VersionPress. This page explains how are 3<sup>rd</sup> party plugins supported.


## General notes

There are two kinds of WordPress plugins:

 1. Those that manipulate the database
 2. Those that don't

For example, a plugin that adds Google Analytics code to the site's HTML is basically just a PHP script and can be versioned just fine by VersionPress. However, if the plugin manipulates the database things get much harder.

There are three possibilities here:

 1. **The plugin works just fine**. This is when the db operations are relatively simple, for example, updating some site option will be captured and understood by VersionPress OK.
 2. **It works, technically, but the change messages are not very helpful**. For example, if an e-commerce solution uses some custom post types and actions, you will see something like *"Updated post of type 'product'"* while the actual action could be much better described as *"Purchased product xyz"*.
 3. **The plugin isn't supported at all**. This can happen e.g. when the plugin uses its own custom database structure or does very non-standard things.

Our goal is to never allow 3 and continually improve 2 so that VersionPress's understanding of external actions becomes better and better but this is rather a long-term goal. At the moment, be careful when using complex third-party plugins.

## Supported plugins

Plugins that are known to work fine, or very close to that:

 - Hello Dolly :-)
 - Akismet


## Unsupported plugins 

Plugins that are known to cause serious issues to VersionPress:

 - None, yet. We will update this section as we receive user reports.


## Partially supported plugins

### Jetpacks

Jetpack is actually a collection of plugins, most of which work fine. Some sub-plugins are of category 2 above, i.e., the actions are technically tracked but the descriptions could be improved. We will be working on that in future releases.