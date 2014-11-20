# Versioning Engine - Git #

Under the hood, VersionPress relies on [Git](http://git-scm.com/) which is one of the most wide-spread version control systems in the world. It was an important decision up-front that brings many benefits but also one trade-off in form of higher system requirements (Git is currently required on the server). This page discusses why we chose Git and why it's such an important decision for the project.


## Why Git ##

Git was a perfect fit for VersionPress for a couple of reasons:

* It is a **world-class, proven system**. Many large and important projects depend on it (Android, Linux and [many others](https://git.wiki.kernel.org/index.php/GitProjects); even WordPress itself has a [Git mirror](https://github.com/WordPress/WordPress)) 
* It is **open source** and **portable** (runs on any operating system)
* Git is **decentralized**. It will happily maintain history on a local computer only without ever sending the data anywhere – important for your privacy.
* It has a huge **community and ecosystem** around it. There are many 3rd party tools and services that work well with Git.


## How this benefits you ##

One practical benefit is that VersionPress repository is just a plain Git repository that you (if you are a power user) can work with the same way as with any other Git repo. For example, you can set up your own Git server and push your work there. Or you can inspect this site history in command line tools.

One thing that is especially worth pointing out is that **you can actively update the repository**. It is not "reserved", "locked" or anything like that for VersionPress. You can even start with an existing Git repo in place – VersionPress will not overwrite it, it will just commit into it. Symmetrically, if you decide to stop using VersionPress you can still continue using the Git repo for later manual commits.

This really opens a whole new world for advanced WordPress admins. 