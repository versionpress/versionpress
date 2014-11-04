# How VersionPress Works #

Sometimes, the best way to understand a product is to have a brief idea of how it works, internally. That way, you know what actions are safe, what is possibly problematic and so on. This page should provide that brief overview.


## High level overview ##

WordPress site with VersionPress installed and activated contains three main parts:

* **WordPress itself**, i.e. the PHP code and MySQL database
* **[Git](./git) repository** that manages the historic revisions
* **Our plugin** that translates WordPress data into the Git format and does all the other things like providing the admin pages etc.

One important point here is that our code actually implements very little versioning logic itself. Instead, we depend heavily on Git which was an important strategic decision. This has many advantages but also poses some new challenges as described [here](./git).

With regards to how VersionPress works, **it generally observes write operations** (e.g., updating a post, adding a comment etc.) and prepares the data in a format that will be suitable for version control in Git. The files in this format are stored in the `wp-content/vpdb` folder and later committed to the Git repository. In the opposite direction, they can be used to update the MySQL database on operations like Undo, Rollback or Clone.

On read operations (displaying a post etc.), VersionPress generally does nothing. It might happen that even something that appears as a read operation changes some data behind the scenes, in which case there will be a new commit.

