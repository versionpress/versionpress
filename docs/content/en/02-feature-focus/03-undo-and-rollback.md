# Undo and Rollback #

Undo and rollback are two essential, most visible features of VersionPress. They share some common behavior but are typically used for different purposes.

<div class="important">
  <strong>Warning</strong>
  <p>Reverts manipulate the database and if there are any kind of problems, the database might be left in a broken state. That is why we strongly recommend having an external site backup at least during the EAP period.</p>
</div>

## Undo ##

The Undo feature can **selectively undo only some changes while keeping the newer updates**. For example, if you edited a post and then updated the site title, undo can revert just the post change without taking the site title with it.

This is a very powerful feature that not many other technologies support – for example, the undo button in text editors is more like our rollback, as are most backup solutions. Right now, you can undo a single change; in the future we will have a way to undo a range of changes, or their selection.


## Rollback ##

**Rollback takes the site to some previous state.** For example, if you roll back to the state where you first installed VersionPress, all your changes done since then will be gone.

Rollback works by creating a **new state** that looks like some previous one. This is a very important concept that means two things:

 1. VersionPress never loses anything from its history. Once something is there, it is always recoverable.
 2. The rollback itself can be easily reverted. It is a change like any other so just click *Undo* next to it if you need.


## When reverts won't work

There are cases when reverts will refuse to work, and rightfully so. Generally, rollback won't cause any problems but undo is more "picky" about when it will work and when not.

These are the main situations that prevent Undo from doing its job:

### Conflicts

Undo will not work when **the change being reverted is in *conflict* with some more recent change**. For example, you want to undo a blog post update for a post that has been deleted in the meantime. Or that text change is in conflict with some newer edit done by some of your colleagues or by yourself. In such cases, no technology can know what's the right way to resolve the conflict. For example, in the text conflict situation it is up to the editor to compare the two versions and choose the better one.

When VersionPress encounters a conflict – and it can detect them reliably – it will just report this to the user and stop doing the revert. We don't have a conflict resolution UI yet – it might come in a future update.


### Invalid entity references

Say that you delete a *comment*, want VersionPress to restore it but the related *post* no longer exists. VersionPress will see this as a **logical conflict in relations between two entities** and will reject the revert. In other words, VersionPress checks "foreign keys" before it proceeds with the undo.

To fix this, first restore the related entity (e.g., the post) and only then the original entity (e.g., the comment).


### Uncommitted files

The last scenario where revert will not work is uncommited changes somewhere in the site files. For example, if you manually edited a theme file and haven't committed this change to Git, revert will be rejected because your changes could be possibly lost. In technical terms, **working directory must be clean for reverts to work.**


### Merge commits

Merge commits join two lines of development back together. For example, if you did some changes in the live environment and some other changes in the staging environment, doing a [pull](../sync/merging) creates a merge commit.

Merge commits cannot be undone because there is no opposite state to them, i.e., there is no logical "reverse change" that we could apply to the current state of the site.

<div class="note">
 
  **Note to Git users**
 
  In Git, you can get rid of the merge commit by doing e.g. `git reset --hard` and it's still the way in VersionPress if you want to **get rid of** a merge. However, the semantics of an **undo** are different, as described above.
 
</div> 