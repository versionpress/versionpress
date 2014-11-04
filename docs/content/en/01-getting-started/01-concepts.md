# Basic Concepts #

VersionPress brings true version control to the world of WordPress. It is a fairly simple concept but if you never worked with it, this page will walk you through the basic ideas, commonly used terms etc. 


## Version control ##

Version control simply means that **historic revisions of some content are stored**. It has many forms, from simple Undo buttons in text editors to advanced systems for managing documents but the basic principle is really that simple.

[Image of version control]

The interesting thing about version control is that its effects are usually much more valuable than one would think. Take Wikipedia, for example. Its content versioning is not just some boring technical thing, it completely changed the way we access knowledge in the internet age. Another example is WordPress itself – it would be nowhere near today if its developers couldn't collaborate using an open source version control system (called Subversion).     

VersionPress brings true version control to WordPress *sites*. While in plain WordPress, actions like updating a plugin, removing a user or something similar would change the site irreversibly, VersionPress adds a simple way to undo those changes. Conceptually, it is a like an undo button in MS Word but for WordPress sites *and* much more powerful.


## Commonly used terms ##

There are some terms that you will see often both in this documentation and in the product itself. Refer to the list below if in doubt.

<dl>

<dt>repository</dt>
<dd>Internal repository of VersionPress where it keeps all the historic versions of a site. Technically, it is the `.git` folder in the root of the site and it is the most important piece of data that VersionPress maintains.</dd>

<dt>undo</dt>
<dd>The undo command reverts a single change on the site (or a set of changes when we have that implemented). Note that unlike the Undo functionality found it most text editors, VersionPress doesn't erase the history but rather creates a new change that does exactly the opposite of the original change.</dd>

<dt>rollback</dt>
<dd>Returns the site to some previous state, or, more precisely, creates a new version of the site that looks exactly like it used to at some point in the past. Rollback is essentially a set of Undo-s from the current state to the chosen past state.</dd>

<dt>a change</dt>
<dd>Basically one line in the VersionPress table. Some change that happened to the site at some point in the past.</dd>

<dt>commit</dt>
<dd>Another, more technical word for a change in the Git terminology.</dd>

<dt>entity</dt>
<dd>For example a post or a comment. These are things that VersionPress tracks. We use this rather abstract term instead of e.g. a "database row" because not all tracked entities do necessarily need to be database rows etc.</dd>

</dl>

