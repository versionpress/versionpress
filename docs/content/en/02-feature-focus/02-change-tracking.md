# Change Tracking #

Most of the time, VersionPress works silently, simply tracking all the important changes and recording them for later use. This page contains some specifics on what is tracked and how. (It might not be entirely complete as there are lots of details for various content types.)



## Posts ##

*Posts* are the main pieces of content in WordPress which includes both posts and pages by default but also some other post types as well. 

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

 * We don't track "revisions" in the WordPress sense, i.e. posts of a special type `revision`. Git versions are our "revisions".
 * Drafts are treated specially because WordPress updates them quite often (easily after every keystroke when typing a title). VersionPress ignores most of such attempts to create new "versions" and only stores a new revision when the draft is saved or eventually published as a full post.
 * Attachments are special types of posts too. VersionPress tracks both the database change related to these post types as well as files created on the disk.

### Postmeta ###

*Postmeta* stores post metadata about posts like the page template used, featured image displayed etc. Some WP-internal postmeta that are not significant for VersionPress (like `_edit_lock`) are ignored.

Tracked actions:

 * Create postmeta
 * Edit postmeta
 * Delete postmeta



## Comments ##

Tracked actions:

 * Create comment
 * Edit comment
 * Delete comment
 * Trash comment
 * Untrash comment

Comment workflows (pending, approved etc.) are going to be supported post-1.0-beta1 release.



## Options ##

Most options are tracked but e.g. transient options and some others are ignored.

Tracked actions:

 * Create option
 * Edit option
 * Delete option



## Users ##

Tracked actions:

 * Create user
 * Edit user
 * Delete user

### Usermeta ###

Quite a lot of user properties are stored as *usermeta*. Most usermeta are tracked, some are intentionally ignored (e.g., session tokens).



## Terms ##

Terms are things like categories or tags (or custom types, depending on term taxonomies).

Tracked actions:

 * Create term
 * Edit term
 * Delete term

### Term taxonomies ###

Defines meaning for terms. Tracked together with terms.

