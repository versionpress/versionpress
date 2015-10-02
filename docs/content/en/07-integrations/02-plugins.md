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

Those that have custom database tables. No specific list at the moment.


## Partially supported plugins

### Jetpack

Jetpack is actually a collection of plugins, most of which work fine. Some sub-plugins are of category 2 above, i.e., the actions are technically tracked but the descriptions could be improved.