# Concepts

Previous sections focused on using VersionPress on a single site, or more precisely, on a single site *instance*. It offers many useful features there but where it truly shines is when it comes to **multiple instances** (*clones* of the WP site, *environments* if you will) and **synchronization** between them.

!!! note "Terminology"
    The terms *clone*, *WP instance*, *installation*, *environment* etc. all represent the same concept and are used interchangeably here.

## Why multiple instances

There are two main reasons why you would want to have two or more instances of a WordPress site:

 1. Safe testing environment (*staging*)
 2. Team workflows

A **safe testing environment** is essential when you have a larger or risky change like trying out a new plugin, changing a theme or upgrading WordPress. While it is true that VersionPress greatly helps with the Undo / Rollback functionality even on the live site, it is even better to test those changes beforehand. The technique of this is sometimes broadly referred to as staging.

Another common scenario is **team work**. On many projects, several people cooperate to get the work done, from developers, designers to copywriters. The best way to organize such work is to have a separate environment for each person involved so that there is no interference or disruption during the development period.

There is a **common problem** though: while it is simple to create multiple instances of a site, it is generally very hard to *merge* them back together. And you need a merge because simply replacing one site with another could lose newer changes there.

That's where VersionPress comes in.


## Cloning and merging with VersionPress

Let's discuss a workflow that seems basic but actually covers almost any real-world scenario – you can use it for staging, team work, hosting the repository on GitHub, almost anything.

!!! tip "See Also"
    The workflow has been showcased in the blog post [VersionPress 2.0: Easy Staging](https://blog.versionpress.net/2015/09/versionpress-2-0-staging/).

**(1)** You start by **[cloning a site](./cloning.md)**. That creates a new site instance that looks exactly like the original one but with its own files and database tables. Technically, it is a separate WordPress installation.

**(2)** Then you **do the work** there. You can experiment with new stuff, you can break things, it doesn't matter as the environment is completely separate and safe. If things go too crazy, you can always start over and clone from the origin again.

Then, at some point, you're happy with the result and  you're going to **(3)** **[merge](./merging.md)** the changes back. In VersionPress' (and Git's) terminology, you're going to **push** or **pull** changes between environments.

In most cases, the merge will be fully automatic and painless. However, a **conflict** might also occur. This happens when two different edits are done to the same piece of data, for example, when two copywriters edit the same paragraph or the site title is updated differently in two environments.

**(4)** In such case, the **conflict needs to be resolved**. This is always a human work and currently, we do not have a user interface for that but because VersionPress is powered by Git and very close to it, you can (and currently need to) do it manually. When the conflict is resolved, you commit the result and run a special synchronization command as discussed in the [merging](./merging.md) topic again.

That's all there is to it, really. Happy cloning & merging!

