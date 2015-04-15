# Basic Concepts #

VersionPress brings true version control to the world of WordPress. It is a fairly simple concept but if you never worked with it, this page will walk you through the basic ideas, commonly used terms etc. 


## Version control ##

Version control simply means that **historic revisions of some content are stored**. It has many forms, from simple Undo buttons in text editors to advanced systems for managing documents but the basic principle is really simple.

<figure>
  <img src="../../media/version-control.png" alt="Version Control" /> 
  <figcaption>© linode from their <a href="https://www.linode.com/docs/applications/development/introduction-to-version-control">very nice intro</a></figcaption>
</figure>

One interesting thing about version control is that **its effects are usually much more valuable than one would think**. Take Wikipedia, for example. Its content versioning is not just some boring technical thing, it completely changed the way human knowledge is gathered and shared. Another example is WordPress itself – it would be nowhere near today if its developers couldn't collaborate using an open source version control system (Subversion in their specific case).     

**VersionPress brings true version control to WordPress *sites*.** While historically, actions like updating plugins, removing users or something similar changed the site irreversibly (and could break it if the update was buggy), VersionPress adds a simple way to undo those changes.

The underlying technology is actually pretty smart and can do much more than that. For example, *staging* is typically a hard thing but VersionPress makes that a breeze using the same techniques that power the Undo. It's just another example of the above: if you have solid version control many hard tasks suddenly appear simple. That's what VersionPress is about.


## Commonly used terms ##

There are some terms that you will see often both in this documentation and in the product itself. Refer to the list below if in doubt.

<dl>

<dt>repository</dt>
<dd>Internal repository of VersionPress where it keeps all the historic versions of a site. Technically, it is the `.git` folder in the root of the site and it is the most important piece of data that VersionPress manages.</dd>

<dt>undo</dt>
<dd>The undo command reverts a single change on the site (or a set of changes when we have that implemented). Note that unlike the Undo functionality found it most text editors, VersionPress doesn't erase the history but rather creates a new change that does exactly the opposite of the original change.</dd>

<dt>rollback</dt>
<dd>Returns the site to some previous state, or, more precisely, creates a new version of the site that looks exactly like it used to at some point in the past. Rollback is essentially a set of Undo-s from the current state to the chosen past state.</dd>

<dt>a change</dt>
<dd>Basically one line in the VersionPress table. Some change that happened to the site at some point in the past.</dd>

<dt>commit</dt>
<dd>Another, more technical word for a change in the Git terminology.</dd>

<dt>entity</dt>
<dd>For example a post or a comment. These are things that VersionPress tracks. We use this rather abstract term instead of e.g. a "database row" because not all tracked entities do necessarily need to be database rows.</dd>

</dl>

