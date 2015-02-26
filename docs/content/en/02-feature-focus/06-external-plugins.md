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

Plugins that are known to cause serious issues:

 - (We will update this section based on user reports)


## Partially supported plugins

### Jetpack

Jetpack is actually a collection of plugins, most of which work fine. Some sub-plugins are of category 2 above, i.e., the actions are technically tracked but the descriptions could be improved.