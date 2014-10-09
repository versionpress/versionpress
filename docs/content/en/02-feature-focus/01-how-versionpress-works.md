# How VersionPress works #

This is a high-level overview of how VersionPress works. If you are a common user you generally don't need to worry but if you're wondering what exactly VersionPress does, this page is for you. 


## High level overview ##

Site with VersionPress installed and activated has three main parts:

* **WordPress itself**, i.e. PHP + MySQL
* **Git repository** that manages all the historic versions
* **Glue code** that translates raw WordPress data into a format that can be managed by Git

Most of the VersionPress code falls under the third point but it's important to note that we have generally very little code to implement the version control itself. We depend on [Git](http://git-scm.com/) to do the heavy lifting for us which has [many advantages](./git). 

There are two more important things with regards to how VersionPress works:

1. **VersionPress only observes write operations**. This is kind of obvious because read operations don't change state and there would be nothing to commit but it is important for performance. Read-heavy sites (most blogs, public websites etc.) are therefore fine. (See also [Performance considerations](./performance).)
2. **MySQL database is only updated by VersionPress on Undo or Rollback**. Storing new versions does not manipulate disk or MySQL data in any way.

