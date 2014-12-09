# Undo and Rollback #

Undo and rollback are two essential, most visible features of VersionPress. They share some common behavior but are typically used in slightly different scenarios.


## Rollback ##

Let's start with the simpler of the two functions. **Rollback creates a new state which is exactly the same as some previous state of the site.** For example, if you roll back to the state where you first installed VersionPress, all your changes done since then will be reverted.

One thing to note in the previous paragraph is that the rollback creates a *new* state that only looks like the previous state of the site. This is a core principle of VersionPress: it never throws away any historic data. This has a benefit that you can easily "undo a rollback" as any other commit should you need to.


## Undo ##

The Undo feature can **selectively undo only some changes while keeping the newer updates**. For example, imagine that you edited a post and then updated the site title. With undo, you can revert just the post change while keeping the new site title.

This is a very powerful feature that not many other technologies have (for example, the Undo button in text editors is more like our rollback). With this power, there is one thing to be aware of: the undo may not always succeed. Imagine that you want to revert a post update but that post no longer exists – you encounter what is called a *conflict*.

In the case of a conflict, VersionPress aborts its work and informs you about it. Your site is left in a healthy state and depending on the situation, you may want to deal with it manually. For example, if you were to undo a paragraph change which has changed since in a newer commit, you may want to inspect the paragraph text, pick which version you like better and update the post manually.

<div class="note">
  <strong>Conflicts do happen</strong>
  <p>Although conflicts are not the most pleasant thing in the world to deal with, they are natural. VersionPress cannot be "fixed" to get rid of them, and moreover, it cannot even know whether there will be a conflict or not until it tries. So you will see the <i>Undo</i>, regardless of whether it will eventually work or not.</p>
</div>
 