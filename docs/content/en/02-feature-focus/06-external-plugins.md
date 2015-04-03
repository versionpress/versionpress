# External Plugins Support

WordPress is a very rich ecosystem and its plugins can do anything and everything. This is a huge advantage but also a big challenge for VersionPress. This page explains how are 3<sup>rd</sup> party plugins supported.


## Overview

There are two kinds of WordPress plugins:

 1. Those that **manipulate the database**
 2. Those that **don't**

Plugins from the latter category are generally fine because they are just files on the disk and that is very easy to version control (in fact, Git was created for this very scenario).

Plugins that manipulate the database are much more of a challenge. There are three possibilities here:

 1. **The plugin works just fine**. This is when the db operations are relatively simple, for example, updating some site option will be captured and understood by VersionPress OK.
 2. **It works, technically, but the change messages are not very helpful**. For example, if an e-commerce solution uses some custom post types and actions, you will see something like *"Updated post of type 'product'"* while the actual action could be much better described as *"Purchased product xyz"*.
 3. **The plugin isn't supported at all**. This can happen e.g. when the plugin uses its own custom database structure or does very non-standard things.

Our goal is to be continually improving support for some of the most popular plugins out there and we also plan to have an API so that external plugins can add their support for VersionPress without us doing any explicit work. That will not be ready until some future update though.  


## Supported plugins

Plugins that are known to work fine, or very close to that, are:

 - All plugins that don't manipulate the database
 - Hello Dolly :-)
 - Akismet


## Unsupported plugins 

Plugins that are known to cause issues:

 - [ACF](http://www.advancedcustomfields.com/)
     - ACF is a popular plugin to manage custom post types and field, and while we support custom post types and fields, ACF does some work on top of that that currently causes VersionPress issues. We will be adding support for this popular plugin in a future update.

Note: the list above is not complete.


## Partially supported plugins

### Jetpack

Jetpack is actually a collection of plugins, most of which work fine. Some sub-plugins are of category 2 above, i.e., the actions are technically tracked but the descriptions could be improved.

<div class="warning">
  <strong>Jetpack 3.4 and security reports</strong>
  <p>JetPack 3.4 introduced a new <a href="http://jetpack.me/2015/03/17/jetpack-3-4-protect-secure-and-simplify/">security feature</a> that sends report every 15 minutes and writes the timestamp in the database, **even if the "Protect" module is disabled**. This means that VersionPress will create a meaningless commit every 15 minutes which is undesirable. The current solution is to manually update the code in <code><a href="http://jetpack.wp-a2z.org/oik_api/jetpackperform_security_reporting/">Jetpack::perform_security_reporting()</a></code> to either disable the functionality or to prolong the period, e.g. to <code>1 * DAY_IN_SECONDS</code>. We will have a fix in a future VersionPress update.</p>
</div>


