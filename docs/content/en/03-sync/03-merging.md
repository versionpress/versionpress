# Merging Sites #

After you [created a clone](./cloning) of a site and done all the changes and testing there, you **push** those changes back to the original site. As part of this push, a **merge** happens that combines the contents of both sites into a single working entity.

VersionPress supports two methods of merging – either from command line using WP-CLI or from the admin pages.


## WP-CLI method ##

This method merges a clone into the original site from command line using WP-CLI. You need to have [WP-CLI installed and working](../feature-focus/wp-cli) on your computer / server. 


Merging is executed from the root of your cloned site like this:

    wp vp push

This will apply all changes made in the clone to the original site.


## GUI method ##

To be implemented

