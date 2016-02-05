# Merging Sites

After you [created a clone](./cloning) of a site and tested the changes, you want to merge them back. Merge is an operation that maintains changes from both environments (as opposed to a copy&paste / replace operation which is destructive by nature) and is achieved by a **pull** command in VersionPress. The result can then be **pushed** to another site instance, for example, the live site.

<div class="note">
 
  **Note**
   
  There is **no 'merge' command**. While the operation indeed does a merge, what you're really doing is pulling and pushing the changes between two environments. Git calls these commands `push` and `pull`, and so do we. `merge` in Git is used for merging between *branches*, not clones, and we might introduce such command in the future when/if we support branches as well.
 
</div>

Merge usually works automatically, however, there might be **conflicts** if a conflicting change has been done to a single piece of data. In such case, you need to **resolve** the conflict manually and commit the result using the `git commit` + `vp apply-changes` commands.


## Pulling and pushing changes

The important thing to realize that there is a *direction* to these commands. You always push / pull between two environments and you need to stand in one of them to run the command, which determines the direction.

**Pull** fetches the changes from the other environment and **does the merge**. Well, that is the most common result but there might also be two others:

 - If there were no concurrent changes in the remote environment, no merge commit is created. The history will remain linear which is a so called "fast-forward" merge in the Git terminology.
 - There might have been merge conflicts, in which case the merge commit is postponed until after the conflicts are resolved. See a separate section below on that.

**Push** is the opposite command but somewhat simpler because **it doesn't do a merge**. It will only succeed if there are no changes in the target environment. You typically push only after a pull.


### Examples

Let's go through a couple of scenarios to see the commands in action.

<div class="note">
 
  **Note**
   
  Push and pull are currently implemented as WP-CLI commands. You need to have [WP-CLI installed and working](../feature-focus/wp-cli) on your machine.
 
</div>

Let's start with the main site, e.g., "live", living in `<some path>/www/live` and served from `http://example.com/live`. We want to create a staging environment so we call:

    wp vp clone --name=staging

That creates a clone in `www/staging`, running at `http://example.com/staging` (configurable, see [cloning](./cloning) for more). Then we do some changes in this staging environment via the web.

When done, we `cd` into the `staging` folder and run:

    # /www/staging
    wp vp pull

We don't need to provide any additional parameters because by default, VersionPress will pull from the environment where this clone originated (in Git's language, it's called the *origin*). If we wanted to be explicit, this would have the same effect:

    # /www/staging
    wp vp pull --from=origin

Here, the use of `origin` doesn't really add any value but sometimes, the `--from` parameter might be useful. For instance, if we wanted to pull changes from the staging environment into the live site, we would run this:

    # /www/live
    wp vp pull --from=staging

In either case, the result is an updated site with both the local changes and the changes pulled from the other environment. A merge was performed here.

The push command is useful when we performed the pull standing in the `staging` folder. In that case, we see the merged environment on the staging site but not on the live site yet. We need to push:

    wp vp push

Again, there is no need to provide the `--to=origin` parameter as `origin` is the default target of our clone. After this command, the live site is updated and looks exactly like the staging clone.



## Resolving conflicts

Conflicts happen when one piece of data is updated in two environments, independently. Conflicts need to be resolved by a human as someone needs to decide which change to keep and which change to discard.

Conflicts can happen during the `pull` command as it is the only one doing the merge. You will be given two options:

 1. **Keep the conflict** so that it can be resolved manually ***and* keep the maintenance mode on** (all merging / synchronization is always done under the maintenance mode; the site cannot be working while the conflict markers are in place). You'll typically choose this in a safe environment like staging or dev where downtime isn't that much of an issue.
 2. **Abort the pull and turn the maintenance mode off**. If you choose this, all will be like the pull never ran. You'll probably want to choose this on the live site where you cannot afford extensive downtime.


To resolve the actual conflict, after you've chosen the first option above, do this:

 1. **Resolve conflict** manually with a standard Git workflow. There are [many](http://githowto.com/resolving_conflicts) [good](https://help.github.com/articles/resolving-a-merge-conflict-from-the-command-line/) [resources](https://www.atlassian.com/git/tutorials/using-branches/git-merge) on this but generally:
      1. Resolve the conflict by editing the text files (in your favorite editor or a merge tool like KDiff3, WinMerge etc.)
      2. Stage files
      3. Commit them
 2. Run the **apply-changes** command: `wp vp apply-changes`

After this, the conflicts are resolved and the resulting state with all the changes applied is visible on the WordPress site.

<div class="tip">
 
  **Tip**
   
  The `vp apply-changes` command can also be used outside of the conflict resolution scenario. For example, if you do a Git revert manually or edit some file in `vpdb`, you can then run `vp apply-changes` to see them reflected in the database and the running site. 
 
</div>
