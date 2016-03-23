---
since: 3.0
---

# Searching History

VersionPress has a powerful search that allows finding commits by certain authors, in certain date ranges and so on. The syntax is inspired by GitHub and Gmail and is relatively straightforward to learn.

<div class="note">
  <strong>Note</strong>
  <p>Search is available since VersionPress 3.0.</p>
</div>


## Examples

Let's start with a couple of examples.

<dl>

<dt><code>hello world</code></dt>
<dl>Finds changes that have the words "hello" and "world" somewhere in the change description (commit message).</dl>

<dt><code>author:joe</code></dt>
<dl>Various operators are supported. This finds changes done by Joe (the search is always case insensitive).</dl>

<dt><code>entity:post action:trash</code></dt>
<dl>Multiple operators can be used at the same time (they are combined using AND).</dl>

<dt><code>hello world author: joe* date:">= 2016-01-01"</code></dt>
<dl>Here you can see a couple of syntax rules in play: you can combine as many operators as you like, wildcards are supported, value containing spaces must be quoted, there are optional spaces after the `:`, etc.</dl>

</dl>


## Syntax

**`operator: val*ue`**
Space after colon is optional, value can be quoted using single `' '` or double quotes `" "`. Quoting is required if the value contains spaces. Wildcards are supported.

**`operator:value1 operator:value2`**
Operators can be repeated, their values are then combined using the logical OR. The only exception is the `date` operator with greater than / less than sign which uses logical AND, for example, `date:>2016-01-01 date:<2016-01-15`.

**`operator1:value operator2:value`**
Multiple operators are combined using logical AND.

**`just text`**
Searches the commit text. Without quotes, it will look for commits containing all of the words. With quotes, it does a strict match (still case in-sensitive).


## Operators

**`author`**
Author of the action. You can use either author name or his/her email. Wildcards are supported.

Some actions are not done by logged-in users, e.g., when someone posts a comment on the website. Those commits are done by a special user who you can search for using `author:nonadmin@example.com`.


**`date`**
Commit date. Use the `RRRR-MM-DD` format, e.g., `date: 2016-01-01`. You can use **greater than / less than operators** such as `date: >=2016-01-01` or a **range operator** `..`, for example, `date: 2016-01-01..2016-02-01`. Either boundary can be replaced with a wildcard, e.g., `date: 2016-01-01..*`.

**`entity`, `action`, `vpid`**
VersionPress identifiers. For example, to show only changed done to users, use `entity:user`. Valid entities are basically WordPress tables without the `wp_` (or custom) prefix and in a singular form.

We currently don't have a good way to generate the definitive list of supported entities and actions so use your intuition. For example, deleting a comment is `entity:comment action:delete`, etc. We'll improve the documentation in the future and possibly also provide auto-complete.

**`vp-tag`**
Looks for an arbitrary VP tag, e.g., `Post-Title` (case insensitive, so `post-title: "hello world"` works as well).

VP tags are pieces of metadata that VersionPress stores with every commit. For example, updating the site title creates a commit message like this:

```
[VP] Edited option 'blogname'

VP-Action: option/edit/blogname

X-VP-Version: 3.0
X-VP-Environment: master
```

You can search for all these pieces of metadata, either in the full form, or without the "VP-" prefix (all the operators are automatically left-wildcarded). Some examples that will work fine against this commit are:

- `action: option/edit/blogname`
- `VP-Action: option/edit/*`
- `Action: */edit/*`
- `vp-version: 3.0`
- `environment: master`
- `X-VP-Environment: master`

You might notice that the `VP-Action` tag can clash with the `action:` operator, but it's handled gracefully. For example, if you search for `entity:post action:publish`, VersionPress will understand that you're using an operator at this point. If you do `action:post/publish`, it will understand that it is the lower-case version of the VP tag, it will add the wildcard automatically for you and it will work just if you searched for `VP-Action: post/publish/*`.


## Current limitations

Search in 3.0 does not support negative search. You cannot say something like "author is NOT Joe". This will most likely come in VersionPress 4.0.
