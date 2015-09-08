# Merging Sites

After you [created a clone](./cloning) of a site and tested all the changes, you **push** those changes back to the original site. As part of this push, a **merge** happens that combines the contents of both sites back into a single site again.

Thought the actual process is a merging, the command that initiates it is called a `push`.


## WP-CLI command

This method merges a clone into the original site from command line using WP-CLI. You need to have [WP-CLI installed and working](../feature-focus/wp-cli) on your computer / server. 


Merging is started from the root *of the cloned site* (!) using this command:

    wp vp push

For the full details of the command options, run:

    wp help vp push


## GUI method ##

GUI method will be added in some future update of VersionPress.

