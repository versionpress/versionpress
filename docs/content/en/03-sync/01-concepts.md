# Concepts #

Previous sections of this documentation focused on using VersionPress on a single site, or more precisely, on a single WordPress installation. It offers many useful features there but where VersionPress truly shines is when it comes to **multiple WordPress installations** and synchronization between them.


## Why you would need multiple installations ##

There are two main motivations for multiple WP installations:

 1. A separate testing environment ("staging")
 2. Working in team


## Testing ("staging") environment ##

WordPress, by default, works this way: you log in to the administration, do some actions and they are immediately reflected on the live site. This is fine for simple changes like updating posts or approving comments but for more involved changes like plugin updates or theme customizations, you generally don't want to "test" your work on the live site. True, VersionPress makes reverting those changes quite painless if things go wrong but it's still better to test everything on some separate site first so that your visitor's experience is not disturbed.

Historically, it has been relatively easy to clone the production environment to some local testing site. What has been hard – and sometimes exceptionally hard – was to get those local changes back into production. If your local updates involved database changes, it was hard to synchronize the local database with the production one.

VersionPress makes this simple by helping with two operations:

 * **Cloning** the site to a testing environment
 * **Merging** the test site data back to the live site

You'll see these two terms used quite often so let's explain them some more here:

### Cloning ###

A clone is a complete separate copy of the site, including its files, database and Git repository. If you throw away your clone, the original site is not affected at all so clones are perfect for testing.

If you know Git, cloning is conceptually similar to `git clone` but VersionPress does a bit more, e.g. populating a separate database with a copy of data etc.

See [Cloning a site](./cloning) for instructions on how to use this feature.


### Merging ###

Merging is a process of combining two sites back into one. Sometimes, merge is really simple (e.g. if one of the sites didn't change at all the "merge" is basically using all the changes from the other site) but it can also be quite complex, sometimes even in conflicts (imagine that you changed a certain paragraph of a post on both live site and test site; no software can automatically detect which changes should "win" and we have a *conflict*).

Merge is what's hard and annoying and what versioning systems like VersionPress or Git greatly help with. See [Merging two sites](./merging).



## Team workflows ##

Another common scenario where site synchronization is very useful is team work – and that team may be as small as two people.

Good news for you here – the concepts are exactly the same as described above. VersionPress doesn't really care if it is creating a clone that we, humans, perceive as a "staging environment", or if it's more of a developer clone like "Peter's dev clone". Technically, it will just create a clone in both cases and later merge the work back, whether it is from "staging" to "production" or from "Peter's work" to "Lucy's work".