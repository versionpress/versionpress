# Change Tracking #

Change tracking is a core feature of VersionPress. This page describes the three main facets of it:

 - [Automatic change tracking](#automatic-change-tracking)
 - [Manual change tracking](#manual-change-tracking)
 - [What's not tracked](#whats-not-tracked)



## Automatic change tracking

Most of the time, VersionPress works silently, simply tracking all the important changes and recording them for later use. This includes both files and database entities like posts or comments. This section contains some specifics on what is tracked and how.

<div class="note">
 
  **Note**
 
  The info below might not be entirely complete as there are lots of details for various content types. Consider it a brief overview.
 
</div>



### Files

Most tracked changes involve database entities which are described below but some actions involve files as well. These are the common situations:

 - **Theme** installations, uninstallations and updates
     - Note: theme files are often edited manually and externally, in some kind of editor, which is not (and cannot be) tracked by VersionPress automatically. Please see [Manual change tracking](#manual-change-tracking).
 - **Plugin** installations, uninstallations and updates
 - **WordPress core** updates
 - **Media** uploads

When any such action happens, VersionPress commits both the database change and a related file(s) change. For example, when installing a plugin, VersionPress will take not that the list of installed plugins has been changed in the database and commit the corresponding plugin files as well.

Note that not all files are versioned because you e.g. don't want to commit cache files, large backup ZIPs etc. Please refer to the [What's not tracked](#whats-not-tracked) section for more.  
 


### Database entities

The biggest added value of VersionPress is in *database change tracking*. This section describes what database entities are tracked and how.


#### Posts

*Posts* are the main pieces of content in WordPress which includes both posts and pages by default but also some other post types as well.

**Custom post types and fields** are generally supported and also work with popular plugins like [ACF](http://www.advancedcustomfields.com/).

Tracked actions:

 * Create post
 * Edit post
 * Create draft
 * Update draft
 * Publish draft
 * Trash post
 * Untrash post
 * Delete post

Special treatment:

 * We don't track WordPress **revisions**, i.e., posts of a type `revision`. Git versions are our revisions – more powerful, space efficient etc.
 * **Drafts** are treated specially because WordPress updates them unnecessarily often. VersionPress ignores most of the internal updates and only stores a new revisions when a draft is saved or eventually published as a full post.
     * (In more detail, the draft is first stored by VersionPress when the post state changes from `auto-draft` to `draft`. This happens either after title is filled in and the field loses focus, or after a couple of seconds timeout. The next revision of that draft is then created every time the user clicks *Save draft*, or, eventually, when he/she publishes the post. New revision is *not* created when the user clicks *Preview*.)  
 * **Attachments** are special types of posts too. VersionPress tracks both the database change related to these post types as well as files created on the disk.

##### Postmeta

*Postmeta* stores post metadata about posts like the page template used, featured image displayed etc. Some WP-internal postmeta that are not significant for VersionPress (like `_edit_lock`) are ignored.

Tracked actions:

 * Create postmeta
 * Edit postmeta
 * Delete postmeta



#### Comments

Tracked actions:

 * Create comment
 * Edit comment
 * Delete comment
 * Trash comment
 * Untrash comment

Comment workflows (pending, approved etc.) are going to be supported post-1.0-beta1 release.



#### Options

Most options are tracked but e.g. transient options and some others are ignored.

Tracked actions:

 * Create option
 * Edit option
 * Delete option



#### Users

Tracked actions:

 * Create user
 * Edit user
 * Delete user

##### Usermeta

Quite a lot of user properties are stored as *usermeta*. Most usermeta are tracked, some are intentionally ignored (e.g., session tokens).



#### Terms

Terms are things like categories or tags (or custom types, depending on term taxonomies).

Tracked actions:

 * Create term
 * Edit term
 * Delete term

##### Term taxonomies

Defines meaning for terms. Tracked together with terms.


#### Other entities

VersionPress tracks everything that goes into the standard WordPress tables, which often covers even 3rd party WP plugins if they use features like custom post types etc. VersionPress doesn't track any custom database tables by default.


## Manual change tracking

VersionPress uses the [Git version control system](./git) under the hood and **treats manual commits exactly the same as auto-generated commits**. This means that you can create Git commits however you want – on the command line, using some GUI tool etc. and those commits will show up in the main VersionPress table as any other changes, with the same options to [undo or rollback](./undo-and-rollback) those changes etc.

We will have a user interface for creating commits manually in some future release but at the moment, you need to use the Git command-line interface or one of the GUI Git clients.

An example of how to utilize manual change tracking is editing a WordPress theme:

 1. You open a theme file (e.g., `wp-content/themes/awesometheme/style.css`) in an editor of your choice
 2. You do some edits to the file and preview the changes in the browser
 3. When happy with the result, you commit the changes using this command:

<!-- end of list, http://meta.stackexchange.com/a/34325/136297 -->

    git add style.css
    git commit -m "Updated awesometheme"

This commit will be available in the main VersionPress table as any other change.


## What's not tracked

There are certain things that VersionPress intentionally omits from versioning:

 - **wp-config.php** – this file is environment-specific which means there would be collisions between various developers, staging/live environments etc. See [Cloning a site](../sync/cloning) for details on how to deal with `wp-config.php`.
 - **VersionPress itself** – the folder `plugins/versionpress` is excluded because you don't want a rollback to take you to a state where VersionPress is outdated and possibly buggy.
 - **Anything in `wp-content` other than plugins, themes and uploads**. Common things in `wp-content` are backup folders, cache directories etc. which should generally not be versioned.
 - Log files, system files etc.


### Updating ignore rules

Ignoring is done using the standard [Gitignore files](http://git-scm.com/docs/gitignore) and VersionPress will try to install appropriate `.gitignore` files upon its activation.

<div class="note">
 
  **Note**
 
  If the installation finds existing `.gitignore` file already in place, it will assume that the site is managed professionally and will not attempt to modify the ignore rules itself. The user will be notified about this.
 
</div>

As an example, let's say that you have a `wp-content/myfolder` folder that you want to track. This is a part of the default `.gitgnore` file which causes the `myfolder` being ignored:

    wp-content/*
    !wp-content/plugins/
    !wp-content/themes/
    !wp-content/uploads/

It basically reads "ignore everything in wp-content *(line 1)* except plugins (line 2), themes (line 3) and uploads (line 4)". To add `myfolder` to tracking, just add a fifth line:

    wp-content/*
    !wp-content/plugins/
    !wp-content/themes/
    !wp-content/uploads/
    !wp-content/myfolder/
