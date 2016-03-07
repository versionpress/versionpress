# Shortcodes Format #

It is necessary to describe shortcodes with relations to some DB entities.
Section `shortcodes` describes the shortcodes themself.
Section `shortcode-locations` describes where can shortcodes appear.

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

Following code:

    shortcodes:
        gallery:
            ids: post
        
says that there is a shortcode called `gallery` and it has an attribute that contains IDs of posts.
Such a shortcode looks like this: `[gallery ids="1,2,3,6,11,20"]`.


Following code:

    shortcode-locations:
        post:
            - post_content

says that shortcodes can appear in field (DB column) `post_content` of posts.
