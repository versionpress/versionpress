# Shortcodes Description Format

Similarly to DB schema, VersionPress needs to understand shortcodes – what DB entities they reference, in which attributes and what contexts are valid for them. We use the `*-shortcodes.neon` file to capture this information.

Here is a full example:

    shortcode-locations:
        post:
            - post_content

    shortcodes:
        gallery:
            id: post
            ids: post
            include:
            exclude: post
        playlist:
            id: post
            ids: post
            include: post
            exclude: post


There are two top-level arrays, `shortcode-locations` and `shortcodes`.


## shortcode-locations

This array describes where the shortcodes can appear. By default, WordPress only allows shortcodes in post content but here's another example of how it could look like if it also supported post title and comments:

    shortcode-locations:
        post:
            - post_content
            - post_title
        comment:
            - comment_content

Note that WordPress doesn't restrict shortcode *type* for various locations, so if some shortcode is supported in e.g. `comment_content`, all shortcodes are.


## shortcodes

This is a list of the actual shortcodes, but only those that contain references to other entities (so things like `[embed]` or `[audio]` are not present). Here's the example again:

    shortcodes:
        gallery:
            id: post
            ids: post
            include: post
            exclude: post
        playlist:
            id: post
            ids: post
            include: post
            exclude: post

For example the `[gallery]` shortcode has four attributes that can contain references, and they all point to the `post` entity (it's an entity, not a table; the table will eventually be something like `wp_posts`).

Note that you don't have to worry about the attribute type, whether it contains a single ID or a list. VersionPress handles both the cases correctly:

    [gallery id="1"]
    [gallery id="1,2,3,6,11,20"]

