# Undo and Rollback #

Undo and rollback are two essential, most visible features of VersionPress. They share some common behavior but are typically used in slightly different scenarios.


## Rollback ##

Let's start with the simpler of the two functions, the rollback. **Rollback creates a new state which is exactly the same as some previous state of a site.** For example, if you roll back to the commit where you first installed VersionPress, all your changes done to the site since then will be reverted.

The one thing to note in the previous paragraph is that the **rollback creates a *new* state** that only happens to look like some previous state of the site. This is a core principle of VersionPress: it never loses any historic data, everything is always added as a new change (this holds true even for the Undo feature below). This has a massive benefit that you can easily "undo a rollback" as any other commit.


## Undo ##

The Undo feature can **selectively undo only some changes while keeping the newer updates**. For example, imagine that you edited the post and then updated the site title. With undo, you can revert just the post change while keeping the site title updated.

This is a very powerful feature that not many other technologies support (for example, the undo button in MS Word is more like our rollback; it takes back all the changes and you don't have a chance to preserve e.g. heading formatting when you want to revert some update in the main text). With this power, there is one thing to be aware of: the Undo may not always succeed. Imagine that you want to revert a post update but that post no longer exists – that is a so called **conflict**.

**In the case of a conflict, VersionPress aborts its work**, informs you about it and does nothing more. Your site is left in a healthy state and depending on the situation, you may want to deal with the situation in some other way. For example, if you were to undo a paragraph change which has changed since (hence causing a conflict), you may want to inspect the paragraph text, pick which version you like most and update the post manually.

<div class="note">
  <strong>Note: conflicts just happen</strong>
  <p>Conflicts are natural things in version control – not pleasant but natural. VersionPress cannot be "fixed" to get rid of them, and moreover, it cannot even know whether there will be a conflict until it tries. So you will see the Undo button next to every change, sometimes it will work, sometimes it will result in a (safe) conflict warning. That is life.</p>
</div>
