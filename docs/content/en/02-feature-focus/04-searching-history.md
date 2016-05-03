---
since: '3.0'
---

# Searching History

VersionPress has powerful search with syntax inspired by GitHub or Gmail. It lets you filter the main table by authors, post types, date ranges etc.

<div class="note">
  <strong>Note</strong>
  <p>Search is available since VersionPress 3.0.</p>
</div>


## Examples

Let's start with a couple of examples.


**`hello world`**<br>
Finds changes that have the words "hello" and "world" somewhere in the change description (commit message).

**`hello world author:joe`**<br>
Search operators are supported, e.g., `author:`. The search is always case insensitive so this will find 'Joe', 'JOE' etc.

**`hello world author: joe* date:">= 2016-01-01"`**<br>
Here you can see a couple of syntax rules in play: you can combine as many operators as you like, wildcards are supported, value containing spaces must be quoted, there are optional spaces after the colon, etc.


## Syntax

**`just text`**, **`"just text"`**, **`w*ldcards`**<br>
Searches the commit text. Without quotes, it will look for commits containing all the words. With quotes, it does a strict match (still case in-sensitive). Single and double quotes are both supported.

**`operator: value`**<br>
Space after colon is optional. Value can be quoted and wildcarded as above.

**`operator:value1 operator:value2`**<br>
Operators can be repeated, their values are then combined using logical OR. For example, you can search for changes done by either Adam or Betty by using `author:Adam author:Betty`. The only exception is `date:` which is AND'd, see below.

**`operator1:value operator2:value`**<br>
Multiple operators are combined using logical AND.

--

All of the syntaxes above can be freely combined.


## Operators

### `author:`

Author of the action. You can use author name or his/her email, wildcards are supported.

There are two special authors:

- `author:nonadmin@example.com` finds anonymous actions like posting a comment on a blog.
- `author:wp-cli` finds actions done via [WP-CLI](http://wp-cli.org/).


### `date:`

Commit date. Recommended format is `YYYY-MM-DD`, e.g., `date: 2016-01-01`, but anything that can be parsed by [`strtotime()`](http://php.net/manual/en/function.strtotime.php) is supported. You can use **greater than / less than operators** such as `date: >=2016-01-01` or a **range operator** `..`, for example, `date: 2016-01-01..2016-02-01`. Either boundary can be replaced with a wildcard, e.g., `date: 2016-01-01..*`.

The `date:` operator has currently some limitations:

- Time portion is ignored.
- Repeating this operator is tricky and we recommend using only a single `date:` at a time. For example, if you searched for `date:2016-01-01 date:2016-01-02` you might expect to see commits from both of the dates, but the result would be empty because `date:` uses logical AND due to technical limitations. You could use the AND logic for something like `date:>2016-01-01 date:<2016-02-01` but we recommend you use the range operator instead.
- You cannot search for two date periods with a gap between them. The range must be continuous.


### `entity:`, `action:`, `vpid:`

All actions tracked by VersionPress are done on some entity (`post`, `user`, `option`, `postmeta` etc.), the action is something like `create` or `delete` and every entity has a unique ID, something like `126BBC0541B14B528C623E32EE1B497C`. You can search for these using the operators above, most commonly by `entity` or `action`.

We currently don't have a good way to generate the definitive list of supported entities, you can see them in the commit messages when using a standard Git client but it's not ideal. We'll have a better way to document this in the future.


### **`arbitrary-vp-tag:`**

VP tags are pieces of metadata that VersionPress stores with each commit. For example, updating the site title creates a commit message like this:

```
[VP] Edited option 'blogname'

VP-Action: option/edit/blogname

X-VP-Version: 3.0
X-VP-Environment: staging
```

You can search for VP tags, either in a full form or without the `VP-` / `X-VP-` prefix. Some examples that will work equally fine against the commit above are:

- `environment: staging`
- `X-VP-Environment: staging`
- `VP-environment: STAGING`
- `action: option/edit/blogname`
- `VP-Action: option/edit/*`
- `Action: */edit/*`
- `vp-version: 3.0`

VP-Action actually gets a bit of a treatment because it is also an operator (see above) and quite useful. You can skip the `/*` wildcard as that is added automatically so something like `action: option/edit` will just work.


## Current limitations

Search in 3.0 does not support negative search. You cannot say something like "author is NOT Joe".
