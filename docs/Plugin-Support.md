# Plugin Support

> :construction: The plugin support is **not stable or fully implemented as long as this warning is here**.

VersionPress needs to understand plugins' data, actions, shortcodes and other things to automatically provide version control for them. If you're a plugin developer, this document is for you.

> Note that *themes* are technically similar and will be supported in pretty much the same way, however, we focus on plugins first.


## Introduction

Plugins are described to VersionPress by a set of files stored in the `.versionpress` folder in the plugin root. (Other discovery options are described in a separate section below.) Those files describe:

- Actions in `actions.yml`
- Database schema in `schema.yml`
- Shortcodes in `shortcodes.yml`
- Hooks are defined in `hooks.php`

All files are optional so for example, if a plugin doesn't define any new shortcodes it can omit the `shortcodes.yml` file. Simple plugins like _Hello Dolly_ might even omit everything; they just need to have the `.versionpress` folder so that VersionPress knows the plugin is supported.

By the way, WordPress itself is described to VersionPress as a set of these files (yes, it is treated as a plugin to itself; how meta!). You can take a look at the source files to draw inspiration from there.


## Actions

Actions describe what the plugin does. For example, WooCommerce might have actions like "product updated", "invoice created" etc. Actions are described in the `actions.yml` file and eventually correspond with the commits that VersionPress automatically creates.

### Brief excurse into VersionPress' automatic change tracking

Actions are at the core of VersionPress' [automatic change tracking](https://docs.versionpress.net/en/feature-focus/change-tracking#automatic-change-tracking). They represent the smallest atomic changes in a WordPress site, like:

- publishing a post
- updating an option
- installing a plugin
- etc.

Actions are stored in Git commit messages like this:

```
Edited option 'blogname'

VP-Action: option/edit/blogname
# other VP tags here...
```

In fact, a commit can contain more actions, for example, if you update two options at once, the commit message may look like this:

```
Edited 2 options

VP-Action: option/edit/blogdescription

VP-Action: option/edit/blogname
```

This brief excurse will help you understand a couple of things about the `actions.yml` format.

### `actions.yml` format

An example from the core WordPress descriptor:

```yaml
post:
  tags:
    VP-Post-Title: post_title
    VP-Post-Type: post_type
  actions:
    create: Created %VP-Post-Type% '%VP-Post-Title%'
    edit:
      message: Edited %VP-Post-Type% '%VP-Post-Title%'
      priority: 12

postmeta:
  tags:
    VP-Post-Id: vp_post_id
    VP-Post-Title: /
  parent-id-tag: VP-Post-Id
  actions:
    ...

theme:
  tags:
    VP-Theme-Name: /
  actions:
    install: Installed theme '%VP-Theme-Name%'
    update: Updated theme '%VP-Theme-Name%'
```

- The top-level elements define **scopes** that group the actions. For example, actions related to WordPress posts are in the `post` scope, theme actions are in the `theme` scope. Note: some scopes are database entities, e.g., `post`, but some are not which is why the more generic term "scope" is used.
- Every scope has typically **two sections: `tags` and `actions`**. Only the `actions` one is required.
- **Tags** are values saved in the commit and can be used in messages to make them more human-readable. For example, _Created post 'Hello World'_ is more helpful than _Created post ID 123_.
- **The `actions` section** contains all actions related to the scope and messages that will be displayed for them. The combination of scope and action, i.e., something like `post/create` or `theme/install`, are the "full action" that can be searched for in the UI.
- An action can have a **priority** which is an integer similar to WordPress filters & hooks (the default value is 10, lower value means higher priority). A more important action beats the less important one if both appear in the same commit, for example, `theme/switch` beats `option/edit`. In practice, this leads to more useful messages for the users. 
- **Meta entities** also contain **`parent-id-tag`** with the name of a tag containing ID of the parent entity.


### Action detection

You might be asking how VersionPress assigns these actions to real changes (say, to a database) in a WordPress site. It works like this:

VersionPress **automatically** detects **three basic actions** – `create`, `edit` and `delete`, based on the SQL query issued to the database. You don't need to do anything if you need just these three basic actions.

For more **specific actions**, e.g., `post/trash` or `comment/approve`, filters are used.

- For standard entities, use the `vp_entity_action_{$entityName}` filter which has three parameters: original action, entity in a state before the action and the same entity in a state after the action.
- For meta entities, use the `vp_meta_entity_action_{$entityName}` filter with five parameters: the extra two are parent entity in a state before and after the action.

**Tags** are automatically extracted from the entity. For example, this snippet:

```yaml
  tags:
    VP-Post-Title: post_title
```

makes sure that the `VP-Post-Title` tag contains the value from the `post_title` database field.

Also, you can alter the tags in a filter `vp_entity_tags_{$entityName}` which takes four parameters: the original tags, entity in a state before the action, entity in a state after the action and the action. Similarly to before, use `vp_meta_entity_tags_{$entityName}` for meta entities with two extra parameters representing parent entity and its states.

### Files to commit with an action

So far we've only talked about action descriptions that are stored in commit _messages_ but what about the actual content?

For database changes, VersionPress automatically commits the corresponding INI file. For example, for a `post/edit` action, a post's INI file is committed.

This behavior is sufficient most of the time, however, some changes should commit more files. For example, when the post is an `attachment`, the corresponding uploaded file should also be committed. For this, the list of files to commit can be filtered using the `vp_entity_files_{$entityName}` or `vp_meta_entity_files_{$entityName}` filter. The callback takes a list of possibly changed files, the entity in a state before the action, the entity in a state after the action and in case of meta entity, also the parent entity in states before and after the action.

The array of files to commit can contain three different types of items:

1. Single file corresponding to an entity:

    ```php
    [
      'type' => 'storage-file',
      'entity' => 'entity name, e.g., post',
      'id' => 'VPID',
      'parent-id' => 'VPID of parent (for meta entities)'
    ]
    ```
    
    VersionPress automatically calculates the right path to the file.

2. All files of an entity type:
    
    ```php
    [
      'type' => 'all-storage-files',
      'entity' => 'entity name, e.g., option'
    ]    
    ```

3. Path on the filesystem:
    
    ```php
    [
      'type' => 'path',
      'path' => 'some/path/with/wildcards/*'
    ]    
    ```

The full example might look something like this:

```php
[
  ['type' => 'storage-file', 'entity' => 'post', 'id' => VPID, 'parent-id' => null],
  ['type' => 'storage-file', 'entity' => 'usermeta', 'id' => VPID, 'parent-id' => user-VPID],
  ['type' => 'all-storage-files', 'entity' => 'option'],
  ['type' => 'path', 'path' => '/var/www/wp/example.txt'],
  ['type' => 'path', 'path' => '/var/www/wp/folder/*']
]
```

For non-database actions, e.g., manipulating with plugins / themes, you can also use filters. TODO.


## Database schema

If the plugin adds custom data into the database it must provide a `schema.yml` file describing the database model. For example, this is how WordPress posts are described:

```
post:
  table: posts
  id: ID
  references:
    post_author: user
    post_parent: post
  mn-references:
    term_relationships.term_taxonomy_id: term_taxonomy
  ignored-entities:
    - 'post_type: revision'
    - 'post_status: auto-draft'
  ignored-columns:
    - comment_count: '@vp_fix_comments_count'
  clean-cache:
    - post: id
```


### Defining entities

The top-level keys in YAML files define entity names such as `post`, `comment`, `user`, `option` or `postmeta`. Entity names use a singular form.

By default, the entity names match database table names without the `wp_` (or custom) prefix. It is possible to specify a different table name using the `table` property:

```
post:
  table: posts
  ...
```

Again, this is prefix-less; something like `wp_` will be added automatically.


### Identifying entities

VersionPress needs to know how to identify entities. There are two approaches and they are represented by either using `id` or `vpid` in the schema:

 * **`id`** points to a standard WordPress auto-increment primary key. **VersionPress will generate VPIDs** (globally unique IDs) for such entities. Most entities are of this type – posts, comments, users etc.

 * **`vpid`** points VersionPress directly to use the given column as a unique identifier and skip the whole VPID generation and maintenance process. Entities of this type **will not have artificial VPIDs**. The `options` table is an example of this – even though it has an `option_id` auto-increment primary key, from VersionPress' point of view the unique identifier is `option_name`.

Examples:

```
post:
  table: posts
  id: ID

option:
  table: options
  vpid: option_name

```

### References

VersionPress needs to understands relationships between entities so that it can update their IDs between environments. There are several types of references, each using a slightly different notation in the schema file.


#### Basic references

The most basic references are "foreign key" ones:

```
references:
  <my_column_name>: <foreign_entity_name>
```

For example, this is what post references look like:

```
post:
  references:
    post_author: user
    post_parent: post
```


#### Value references

Value references are used when a reference to an entity depends on another column value. For example, options might point to posts, terms or users and it will depend on which option it is.

The syntax is:

```
value-references:
  <source_column_name>@<value_column_name>:
    <source_column_value>: <foreign_entity_name | @mapping_function>
    <source_column_value>["path-in-serialized-objects"][/\d+/][0]: <foreign_entity_name | @mapping_function>
    <columns_with_prefix_*>: <foreign_entity_name | @mapping_function>
```

As you can see, there are quite a few options. The simplest are static values which are used e.g. by options:


```
option:
  value-references:
    option_name@option_value:
      page_on_front: post
      default_category: term
      ...
```

If the entity type needs to be determined dynamically it can reference a PHP function:

```
postmeta:
  value-references:
    meta_key@meta_value:
      _menu_item_object_id: '@\VersionPress\Database\VpidRepository::getMenuReference'
```

Note that there are no parenthesis at the end of this (it's a method reference, not a call) and that it is prefixed with `@`. The function gets the entity as a parameter and returns a target entity name. For example, for `_menu_item_object_id`, the function looks for a related DB row with `_menu_item_type` and returns its value.
 
If the ID is in a serialized object, you can specify the path by a suffix of the source column. It looks like array access but also supports regular expressions, for example:

```
option:
  value-references:
    option_name@option_value:
      widget_pages[/\d+/]["exclude"]: post
```

To visualize this, the `widget_pages` option contains a value like `a:2:{i:2;a:3:{s:5:"title";s:0:"";s:7:"exclude";s:7:"1, 2, 3";...}...}` which, unserialized, looks like this:

```
[
  2 => [
    "title" => "",
    "sortby" => "post_title",
    "exclude" => "1, 2, 3"
  ],
  "_multiwidget" => 1
]
```

The schema says that the numbers in the "exclude" key point to posts.

Value references also support wildcards in the name of the source column. It's useful e.g. for options named `theme_mods_<name of theme>`. An example that mixes this with the serialized data syntax is:

```
option:
  value-references:
    option_name@option_value:
      theme_mods_*["nav_menu_locations"][/.*/]: term
      theme_mods_*["header_image_data"]["attachment_id"]: post
      theme_mods_*["custom_logo"]: post
```


#### M:N references

Some entities are in an M:N relationship like posts and term_taxonomies. The format to capture this is:

```
mn-references:
  <junction_table_name_without_prefix>.<column_name>: <foreign_entity_name>
```

This is a concrete example from the post entity:

```
post:
  mn-references:
    term_relationships.term_taxonomy_id: term_taxonomy
```

One entity is considered "master" which is kind of arbitrary (technically, they are equal) but here, we decided that posts will store tags and categories, not the other way around. INI files of posts will store the references.

References can also be prefixed with a tilde (`~`) which makes them virtual:

```
mn-references:
  ~<junction_table_name_without_prefix>.<column_name>: <foreign_entity_name>
```

Virtual references are not stored in INI files but the relationships are checked during reverts. For example, when a revert would delete a category (revert of `term_taxonomy/create`) and there is some post referencing it, the operation would fail.


#### Parent references

Some entities are stored within other entities, for example, postmeta are stored in the same INI file as their parent post. This is captured using a `parent-reference` property:

```
postmeta:
  parent-reference: post_id
  references:
    post_id: post

```

This references one of the basic reference column names, not the final entity. The notation above reads "postmeta stores a parent reference in the `post_id` column and that points to the `post` entity".


### Frequently written entities

Some entities are changed very often, e.g., view counters, Akismet spam count, etc. VersionPress only saves them once in a while and the `frequently-written` section influences this:

```
entity:
  frequently-written:
    - 'column_name: value'
    - query: 'column1_name: value1 column2_name: value2'
      interval: 5min
```

The values in the `frequently-written` array can either be strings which are then interpreted as queries, or objects with `query` and `interval` keys. Queries use the same syntax as search / filtering in the UI, with some small differences like that the date range operator cannot be used but overall, the syntax is pretty intuitive. The interval is parsed by the `strtotime()` PHP function and the default value is one hour.


### Ignoring entities

Some entities should be ignored, i.e., not tracked at all, like transient options, environment-specific things etc. This is an example from the `option` entity:

```
  ignored-entities:
    - 'option_name: _transient_*'
    - 'option_name: _site_transient_*'
    - 'option_name: siteurl'
```

Again, queries are used. Wildcards are supported.


#### Ignoring columns

It is possible to ignore just parts of entities – their columns. The columns might either be ignored entirely or computed dynamically using a PHP function:

```
entity:
  ignored-columns:
    - column_name_1
    - column_name_2
    - computed_column_name: '@functionReference'
```

The function is called whenever VersionPress does its INI files => DB synchronization. The function will get an instance of `VersionPress\Database\Database` as an argument and is expected to update the database appropriately.

#### Cache invalidation

WordPress uses cache for posts, comments, users, terms, etc. This cache needs to be invalidated when VersionPress updates database (on undo, rollback, pull, etc.). It is possible to tell VersionPress which cache has to be invalidated and where it finds related IDs. For example, when some post is deleted using *undo*, it is necessary to call `clean_post_cache(<post-id>)`. VersionPress will do it automatically based on following configuration:

```
post:
  table: posts
  id: ID
  clean-cache:
    - post: id
```

It tells VersionPress to delete the post cache (VP resolves the function name as `clean_<cache-type>_cache`). You can use `id` as the source of IDs for invalidation or a reference. For example like this:

```
post:
  table: posts
  id: ID
  references:
      post_author: user
      post_parent: post
  clean-cache:
    - post: id
    - user: post_author
    - post: post_parent
```




## Shortcodes

TODO: inline [shortcodes readme](../plugins/versionpress/.versionpress/shortcodes-readme.md) here.

## Ignored folders

Feel free to use custom `.gitignore` for files in the plugin directory. You can also ignore files / directories outside the plugin directory. There is a filter to let VersionPress know which files / directories you want to ignore (TODO).


## Discovery mechanism

TODO


## Public API – Hooks and functions

### Filters

 - `vp_entity_action_{$entityName}`
   - `apply_filters("vp_entity_action_{$entityName}", $action, $oldEntity, $newEntity)`
 - `vp_meta_entity_action_{$entityName}`
   - `apply_filters("vp_meta_entity_action_{$entityName}", $action, $oldEntity, $newEntity, $oldParentEntity, $newParentEntity)`
 - `vp_entity_tags_{$entityName}`
   - `apply_filters("vp_entity_tags_{$entityName}", $tags, $oldEntity, $newEntity, $action)`
 - `vp_meta_entity_tags_{$entityName}`
   - `apply_filters("vp_meta_entity_tags_{$entityName}", $tags, $oldEntity, $newEntity, $action, $oldParentEntity, $newParentEntity)`
 - `vp_entity_files_{$entityName}`
   - `apply_filters("vp_entity_files_{$entityName}", $files, $oldEntity, $newEntity)`
 - `vp_meta_entity_files_{$entityName}`
   - `apply_filters("vp_meta_entity_files_{$entityName}", $files, $oldEntity, $newEntity, $oldParentEntity, $newParentEntity)`
 - `vp_entity_should_be_saved_{$entityName}`
   - `apply_filters("vp_entity_should_be_saved_{$entityName}", $shouldBeSaved, $data, $storage)`
 - `vp_bulk_change_description_{$entityName}`
   - `apply_filters("vp_bulk_change_description_{$entityName}", $description, $action, $count, $tags)`
 - `vp_action_description_{$scope}`
   - `apply_filters("vp_action_description_{$scope}", $message, $action, $vpid, $tags)`

### Actions

 - `vp_before_synchronization_{$entityName}`
   - `do_action("vp_before_synchronization_{$entityName}")`
 - `vp_after_synchronization_{$entityName}`
   - `do_action("vp_after_synchronization_{$entityName}")`

### Functions

 - `vp_force_action`
   - `vp_force_action($scope, $action, $id = '', $tags = [], $files = [])`

## Resources

- Issue [#1036](https://github.com/versionpress/versionpress/issues/1036) – everything was discussed there.
