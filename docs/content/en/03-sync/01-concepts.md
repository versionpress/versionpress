# Concepts

Previous sections of this documentation focused on using VersionPress on a single site, or more precisely, on a single WordPress installation. It offers many useful features there but where VersionPress truly shines is when it comes to **multiple WordPress installations** (*clones* of the main site) and **synchronization** between them.

<div class="note">
 
  **Note about terminology**
 
  The terms *clone* or a WP *instance* or WP *installation* all mean the same – simply a copy of a site that can run independently. From the VersionPress' point of view, all clones are equal, even though we, human beings, like to call certain installations as "main", "master" or whatever. VersionPress doesn't care.  
 
</div>


## Why multiple clones

There are two main reasons why you would want to have two or more instances of a site:

 1. Safe testing environment (a technique also called *staging*)
 2. Team workflows


### Testing environment (staging)

A safe testing environment is essential when you have a larger or risky change for the site like trying out a new plugin, changing a theme or upgrading WordPress to a newer version. While it is true that VersionPress greatly helps with the Undo / Rollback functionality even on the live site, testing these changes first is a much better idea.


### Team workflows 

Another common scenario where site synchronization is very useful is team work. On bigger projects, it is common that several people cooperate to get the site done, from developers, designers to copywriters. If each of them can have their own clone of a site that is not being messed up with by anyone else, they can focus on their work and VersionPress will then take care of the difficult and often painful experience of merging the work back together.


## Cloning and merging

Both of the cases above are backed by the same approach – creating clones and merging them back. It is really this simple and there is only a few concepts to understand.

For instance, if you want to test some changes safely:

 1. You first **clone** the site to a new, safe location
 2. You test the changes there
 3. From the clone, you **push** the changes back to the main site

The push will attempt to automatically **merge** the sites, i.e., if the files or the database changed in both instances, VersionPress will attempt keep changes from both of them. This will work automatically unless there is a conflict.

A **conflict** is a situation where two people made a conflicting change to the same piece of data; for instance, if Alice changed the site title to "I like this new title" while Bob changed it to "This is way better", there is a conflict that needs to be resolved. *(Like in real life; wait, this is real life!)*

**Conflict resolution** always needs to be done manually because there is a true, logical conflict in people's intentions. Conflict resolution always happens in the clone so that the original site is not affected. After the conflict is resolved, you push / merge again which will now succeed.

*The truth is that conflict resolution is not always easy, neither in real life nor in software. We currently do not have an user interface that would help with this so you need to drop to Git to resolve the conflicts manually. But we will make the experience smoother in some future update.*

And that's all you need to understand. Now, you just need to know how to do that, and that is described in these topics:

 - [Cloning a site](./cloning)
 - [Merging two sites](./merging)

