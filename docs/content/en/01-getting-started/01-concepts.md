# Basic Concepts #

This page is an overview of how VersionPress works and what it provides.

<div class="note">
  <strong>Note: This will be reworked</strong>
  <p>Feel free to carry on to <a href="./installation-uninstallation">Installation and Uninstallation</a>.</p>
</div>


## Version control ##

Basically, what VersionPress brings to the world of WordPress sites is a true version control. This means that VersionPress tracks all the changes that happen to a site and offers the chance to undo them or roll back to some previous state of the site.

Once you have all the changes tracked, many nice and useful things are suddenly possible. For instance, VersionPress can sync two versions of a site, for example local development version and the production version, while automatically taking care of what would normally become sync conflict. Team work is possible. Selective undos are possible. Having proper version control in place is simply tremendously useful.


## Terms used ##

VersionPress uses some terms that might need explanation. If you are ever confused about their exact meaning please consult the list below:


### 'the repository'

Internal repository of VersionPress where it keeps all the historic versions of a site. Technically, it is the `.git` folder in the root of the site and it is the most important piece of data that VersionPress creates and maintains.


### 'undo'

The undo command reverts a single change on the site (or a set of changes when we have that implemented). Note that unlike the Undo functionality found it most text editors, VersionPress doesn't erase the history but rather creates a new change that does exactly the opposite of the original change.

### 'rollback'

Returns the site to some previous state, or, more precisely, creates a new version of the site that looks exactly like it used to at some point in the past. Rollback is essentially a set of Undo-s from the current state to the chosen past state.

### 'a change'

Basically one line in the VersionPress table. Some change that happened to the site at some point in the past.

### 'a commit'

Another, more technical word for a change in the Git terminology.

### 'an entity'

For example a post or a comment. These are things that VersionPress tracks. We use this rather abstract term instead of e.g. a "database row" because not all tracked entities do necessarily need to be database rows etc.

### Git

Git is a version control system that powers VersionPress behind the scenes. Find out more about it at http://git-scm.com/.





