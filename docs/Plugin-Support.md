# Plugin Support

> :construction: Plugin support is the main theme of [VersionPress 4.0](https://blog.versionpress.net/2016/10/versionpress-4-0-alpha/) which is currently in alpha. Plugin developers, we'd like your feedback on this, feel free to [open new issues](https://github.com/versionpress/versionpress/issues/new) or chat with us [on Gitter](https://gitter.im/versionpress/versionpress).

VersionPress needs to understand plugin data, actions, shortcodes and other things to automatically provide version control for them. This document describes how plugins (and themes, later) can hook into VersionPress functionality.


## Introduction

Plugins are described to VersionPress by a set of files stored in the `.versionpress` folder in the plugin root (with other discovery options available, see below). They include:

- `actions.yml` – plugin actions, i.e., what the plugin does
- `schema.yml` – database schema (how the plugin stores data)
- `shortcodes.yml` – shortcodes
- `hooks.php` – other hooks

All files are optional so for example, if a plugin doesn't define any new shortcodes it can omit the `shortcodes.yml` file. Simple plugins like _Hello Dolly_ might even omit everything.

> :sparkles: **Tip**: WordPress core is described using the very same format and you can find the definition files in the [`.versionpress`](../plugins/versionpress/.versionpress) folder inside the plugin.


## Actions

Actions represent what the plugin does. For example, WordPress core has actions like "update option", "publish post" and many others. They are the smallest changes in a WordPress site and are eventually stored as Git commits by VersionPress.

An action is identified by a string like `option/edit` or `post/publish`, commits some file(s) with it and has a human-readable message like "Updated option blogname", "Published post Hello World", etc.

Some commits may even contain multiple actions. For example, if a user switches to a new theme that also creates some options of its own, a single commit with `theme/switch` and several `option/create` actions will be created. When this operation is undone, it takes back both the theme switching and options creation.

Actions are described in the `actions.yml` file.


### `actions.yml`

Here's an example from the [WordPress core `actions.yml` file](../plugins/versionpress/.versionpress/actions.yml):

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

These are the main elements:

- The top-level elements are **scopes** that basically group related actions together. For example, actions related to posts are in the `post` scope, theme actions are in the `theme` scope, etc. Scopes use a singular form.
- **Tags** are values saved in commit messages and are typically used to make user-facing messages more useful. For example, it's better to display _Created post 'Hello World'_ than _Created post 123_ and tags make this possible.
    - Tags are either mapped to database fields as in the `post` example, or use the `/` character to indicate that the value is provided by a filter (see below).
- **The `actions` section** defines all actions of a scope.
    - An action has a **message** that can reference tags to make it more user-friendly. Messages use past tense.
    - Each action has a **priority** – 10 by default. Priorities behave like on WordPress filters and actions: the lower the number, the higher the priority. A more important action beats the less important one if both appear in the same commit. For example, `theme/switch` beats `option/edit` which means that the user will see a message about changing themes, not updating some internal option.
    - Priorities can be set dynamically using the `vp_action_priority_{$scope}` filter, see [WPLANG handling](https://github.com/versionpress/versionpress/blob/7a500248a363472127c93a2ffffaec11f486e6e5/plugins/versionpress/.versionpress/hooks.php#L160-L166) as an example.
    - A combination of a scope and an action, e.g., `post/create` or `theme/install`, uniquely identifies the action and can be [searched for in the UI](https://docs.versionpress.net/en/feature-focus/searching-history).
- An action has a **message**, usually in past tense, and a **priority**. If priority is not set, the default value of 10 is used.
    - Priorities behave like on WordPress filters and actions: the lower the number, the higher the priority. A more important action beats the less important one if both appear in the same commit. For example, `theme/switch` beats `option/edit` which means that the user will see a message about changing themes, not updating some internal option.
- **Meta entities** also contain **`parent-id-tag`** with the name of a tag containing ID of the parent entity.


### Action detection

There are generally two types of actions:

- **Database actions** like manipulating posts, options, users, etc.
- **Non-database actions** like updating WordPress, deleting themes, etc.

**Database actions** are more common (at least in WordPress core) and get a pretty convenient treatment by default. Based on the SQL query issued, a `create`, `edit` or `delete` action is created automatically.

If you need more specific actions like `post/trash` or `comment/approve`, filters are used: [`vp_entity_action_{$entityName}`](https://github.com/versionpress/versionpress/blob/0a29069de769841ed545556cecf4d2323a92741b/plugins/versionpress/src/Storages/DirectoryStorage.php#L225-L225) for standard entities and [`vp_meta_entity_action_{$entityName}`](https://github.com/versionpress/versionpress/blob/49fdc0ba737b40560c40129d791e0cf63b1031e0/plugins/versionpress/src/Storages/MetaEntityStorage.php#L166-L16) for meta entities.

> 🚧 Hooks are not properly documented yet, please click through the hook names to at least browse the source codes on GitHub.

Tags are automatically extracted from the database entity. For example,

```yaml
  tags:
    VP-Post-Title: post_title
```

makes sure that the message (defined as `Created post '%VP-Post-Title%'`) automatically stores the real post title.

Tags can be altered (or created entirely if the YAML only uses `/` as a tag value) by filters [`vp_entity_tags_{$entityName}`](https://github.com/versionpress/versionpress/blob/0a29069de769841ed545556cecf4d2323a92741b/plugins/versionpress/src/Storages/DirectoryStorage.php#L226-L226) and [`vp_meta_entity_tags_{$entityName}`](https://github.com/versionpress/versionpress/blob/49fdc0ba737b40560c40129d791e0cf63b1031e0/plugins/versionpress/src/Storages/MetaEntityStorage.php#L167-L16).

**Non-database actions** are tracked manually by calling a global [`vp_force_action()`](https://github.com/versionpress/versionpress/blob/3b0b242b11804d39c838b15a21ffbd7a27b404b4/plugins/versionpress/public-functions.php#L18-L18) function. This overwrites all other actions VersionPress might have collected during the request. For example, this is how `wordpress/update` action is tracked:

```
vp_force_action('wordpress', 'update', $version, [], $wpFiles);
```

> :construction: We're planning to change this for the final VersionPress 4.0 release. Some filter will probably be used instead.


### Files to commit with an action

Every action has a message and some content. It's this content that is undone when the user clicks the Undo button in the UI.

For **database actions**, VersionPress automatically commits the corresponding INI file. For example, for a `post/edit` action, a post's INI file is committed.

> Side note: VersionPress stores database entities in the `wp-content/vpdb` folder as a set of INI files.

This behavior is sufficient most of the time, however, some changes should commit more files. For example, when the post is an attachment, the uploaded file should also be committed. For this, the list of files to commit can be filtered using the `vp_entity_files_{$entityName}` or `vp_meta_entity_files_{$entityName}` filters.

The array of files to commit can contain three different types of items:

> Note: Concepts like VPIDs are explained in the "Database schema" section below.

1. Single file corresponding to an entity, for example:

    ```php
    [
      'type' => 'storage-file',
      'entity' => 'post',
      'id' => $vpid,
      'parent-id' => $parentVpid  // for meta entities
    ]
    ```
    
    VersionPress automatically calculates the right path to the file.

2. All files of an entity type:
    
    ```php
    [
      'type' => 'all-storage-files',
      'entity' => 'option'
    ]    
    ```

3. Path on the filesystem:
    
    ```php
    [
      'type' => 'path',
      'path' => 'some/path/supports/wildcards/*'
    ]    
    ```

The full example might look something like this:

```php
[
  ['type' => 'storage-file', 'entity' => 'post', 'id' => $vpid, 'parent-id' => null],
  ['type' => 'storage-file', 'entity' => 'usermeta', 'id' => $vpid, 'parent-id' => $userVpid],
  ['type' => 'all-storage-files', 'entity' => 'option'],
  ['type' => 'path', 'path' => '/var/www/wp/example.txt'],
  ['type' => 'path', 'path' => '/var/www/wp/folder/*']
]
```

For **non-database actions**, this list is one of the arguments of the [`vp_force_action()`](https://github.com/versionpress/versionpress/blob/3b0b242b11804d39c838b15a21ffbd7a27b404b4/plugins/versionpress/public-functions.php#L18-L18) function.

> As noted above, we'll be getting rid of this approach so this is temporary info.

## Database schema

If the plugin adds custom data into the database it must provide a `schema.yml` file describing the database model. For example, this is how WordPress posts are described:

```yaml
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

The top-level keys define entities such as `post`, `comment`, `option` or `postmeta`. Entity names use a singular form.

By default, entity names match database table names without the `wp_` (or custom) prefix. It is possible to specify a different table using the `table` property:

```yaml
post:
  table: posts
  ...
```

Again, this is prefix-less; `wp_` or another prefix will be added automatically.


### Identifying entities

VersionPress needs to know how to identify entities. There are two approaches and they are represented by either using a `id` or `vpid` property in the schema:

 * **`id`** points to a standard WordPress auto-increment primary key. **VersionPress will generate VPIDs** (globally unique IDs) for such entities. Most entities are of this type – posts, comments, users etc.

 * **`vpid`** points VersionPress directly to use the given column as a unique identifier and skip the whole VPID generation and maintenance process. Entities of this type **will not have artificial VPIDs**. The `options` table is an example of this – even though it has an `option_id` auto-increment primary key, from VersionPress' point of view the unique identifier is `option_name`.

Examples:

```yaml
post:
  table: posts
  id: ID

option:
  table: options
  vpid: option_name

```

### References

VersionPress needs to understand relationships between entities so that it can update their IDs between environments. There are several types of references, each using a slightly different notation in the schema file.

#### Basic references

The most basic references are "foreign keys". For example:

```yaml
post:
  references:
    post_author: user
    post_parent: post
```

This says that the `post_author` field points to a user while the `post_parent` references another post.

#### Value references

Value references are used when a reference to an entity depends on another column value. For example, options might point to posts, terms or users and it will depend on which option it is. This is how it's encoded:

```yaml
option:
  value-references:
    option_name@option_value:
      page_on_front: post
      default_category: term
      ...
```

This is the simplest case but it can also get more fancy:

If the entity type needs to be **determined dynamically** it can reference a PHP function:

```yaml
postmeta:
  value-references:
    meta_key@meta_value:
      _menu_item_object_id: '@\VersionPress\Database\VpidRepository::getMenuReference'
```

Note that there are no parenthesis at the end of this (it's a method reference, not a call) and that it is prefixed with `@`. The function gets the entity as a parameter and returns a target entity name. For example, for `_menu_item_object_id`, the function looks for a related DB row with `_menu_item_type` and returns its value.
 
If the **ID is in a serialized object**, you can specify the path by a suffix of the source column. It looks like an array access but also supports regular expressions, for example:

```yaml
option:
  value-references:
    option_name@option_value:
      widget_pages[/\d+/]["exclude"]: post
```

To visualize this, the `widget_pages` option contains a value like `a:2:{i:2;a:3:{s:5:"title";s:0:"";s:7:"exclude";s:7:"1, 2, 3";...}...}` which, unserialized, looks like this:

```php
[
  2 => [
    "title" => "",
    "sortby" => "post_title",
    "exclude" => "1, 2, 3"
  ],
  "_multiwidget" => 1
]
```

The schema says that the numbers in the "exclude" key reference posts.

Value references also support **wildcards** in the name of the source column. It's useful e.g. for options named `theme_mods_<name of theme>`. An example that mixes this with the serialized data syntax is:

```yaml
option:
  value-references:
    option_name@option_value:
      theme_mods_*["nav_menu_locations"][/.*/]: term
      theme_mods_*["header_image_data"]["attachment_id"]: post
      theme_mods_*["custom_logo"]: post
```

It probably won't surprise you that this is a real example used in WordPress' `schema.yml`. :stuck_out_tongue_winking_eye:

Another supported feature are IDs in serialized data in serialized data (really).

An example from WooCommerce: `a:1:{s:4:"cart";s:99:"a:1:{s:32:"a5bfc9e07964f8dddeb95fc584cd965d";a:2:{s:10:"product_id";i:37;s:12:"variation_id";i:0;}}";}`.

```yaml
session:
  value-references:
    session_key@session_value:
      "*[\"cart\"]..[/.*/][\"product_id\"]": product
      "*[\"cart\"]..[/.*/][\"variation_id\"]": variation
```

The complete syntax is:

```yaml
value-references:
  <source_column_name>@<value_column_name>:
    <source_column_value>: <foreign_entity_name | @mapping_function>
    <source_column_value>["path-in-serialized-objects"][/\d+/][0]..["key-in-nested-serialized-array"]: <foreign_entity_name | @mapping_function>
    <columns_with_prefix_*>: <foreign_entity_name | @mapping_function>
```

#### M:N references

Some entities are in an M:N relationship like posts and term_taxonomies. This is how it's written:

```yaml
post:
  mn-references:
    term_relationships.term_taxonomy_id: term_taxonomy
```

One entity is considered the main one which is kind of arbitrary as technically, VersionPress treats them equally. Here, we decided that posts will store tags and categories in them, not the other way around.

The syntax is:

```yaml
mn-references:
  <junction_table_name_without_prefix>.<column_name>: <foreign_entity_name>
```

References can also be prefixed with a tilde (`~`) which makes them **"virtual"**:

```yaml
mn-references:
  ~<junction_table_name_without_prefix>.<column_name>: <foreign_entity_name>
```

A virtual reference is not stored in the INI file but the relationships are still checked during reverts. For example, when a revert would delete a category (revert of `term_taxonomy/create`) and there is some post referencing it, the operation would fail. This is ensured by:

```yaml
term_taxonomy:
  mn-references:
    ~term_relationships.object_id: post
```


#### Parent references

Some entities are stored within other entities, for example, postmeta are stored in the same INI file as their parent post. This is captured using a `parent-reference` property:

```yaml
postmeta:
  parent-reference: post_id
  references:
    post_id: post

```

This references one of the basic reference column names, not the final entity. The notation above reads "postmeta stores a parent reference in the `post_id` column, and that points to the `post` entity".


### Frequently written entities

Some entities are changed very often, e.g., view counters, Akismet spam count, etc. VersionPress only saves them once in a while and the `frequently-written` section influences this:

```yaml
entity:
  frequently-written:
    - 'column_name: value'
    - query: 'column1_name: value1 column2_name: value2'
      interval: 5min
```

The values in the `frequently-written` array can either be strings which are then interpreted as queries, or objects with `query` and `interval` keys.

- **Queries** use the same syntax as search / filtering in the UI, with some small differences like that the date range operator cannot be used but overall, the syntax is pretty intuitive. _TODO add link_
- The **interval** is parsed by the `strtotime()` function and the default value is one hour.


### Ignoring entities

Some entities should be ignored (not tracked at all) like transient options, environment-specific options, etc. This is an example from the `option` entity:

```yaml
  ignored-entities:
    - 'option_name: _transient_*'
    - 'option_name: _site_transient_*'
    - 'option_name: siteurl'
```

Again, queries are used. Wildcards are supported.


#### Ignoring columns

It is possible to ignore just parts of entities. The columns might either be ignored entirely or computed dynamically using a PHP function:

```yaml
entity:
  ignored-columns:
    - column_name_1
    - column_name_2
    - computed_column_name: '@functionReference'
```

The function is called whenever VersionPress does its INI files => DB synchronization. The function will get an instance of `VersionPress\Database\Database` as an argument and is expected to update the database appropriately. The `Database` class has the same methods as `wpdb` but the changes it make are not tracked by VersionPress itself.

#### Cache invalidation

WordPress uses cache for posts, comments, users, terms, etc. This cache needs to be invalidated when VersionPress updates database (on undo, rollback, pull, etc.). It is possible to tell VersionPress which cache to invalidate and where to find the related IDs.

For example, when some post is deleted using the Undo functionality, it is necessary to call `clean_post_cache(<post-id>)`. VersionPress will do it automatically based on following piece of schema:

```yaml
post:
  table: posts
  id: ID
  clean-cache:
    - post: id
```

It tells VersionPress to delete the post cache (VP resolves the function name as `clean_<cache-type>_cache`). You can use `id` as the source of IDs for invalidation or a reference. For example like this:

```
post:
  references:
      post_author: user
      post_parent: post
  clean-cache:
    - post: id
    - user: post_author
    - post: post_parent
```


## Shortcodes

Similarly to database schema, VersionPress needs to understand shortcodes as they can also contain entity references. `shortcodes.yml` describes this, here is an example:

```yaml
shortcode-locations:
  post:
    - post_content

shortcodes:
  gallery:
    id: post
    ids: post
    include:
    exclude: post
  playlist:
    id: post
    ids: post
    include: post
    exclude: post
```

The **`shortcode-locations`** array tells VersionPress where the shortcodes can appear. By default, WordPress only allows shortcodes in post content but here's an example of how it could look if it also supported them in post titles and comments:

```yaml
shortcode-locations:
  post:
    - post_content
    - post_title
  comment:
    - comment_content
```

Note that WordPress doesn't restrict shortcode *type* for various locations, so if some shortcode is supported in e.g. `comment_content`, all shortcodes are.

The `shortcodes` array holds the actual shortcodes, but only those that contain references to other entities so things like `[embed]` or `[audio]` are not present. Here's an example:

```yaml
shortcodes:
  gallery:
    id: post
    ids: post
    include:
    exclude: post
  playlist:
    id: post
    ids: post
    include: post
    exclude: post
```

For example the `[gallery]` shortcode has four attributes that can contain references, and they all point to the `post` entity (it's an entity, not a table; the table will eventually be something like `wp_posts`).

Note that you don't have to worry about the attribute type, whether it contains a single ID or a list of IDs. VersionPress handles both cases automatically:

```
[gallery id="1"]
[gallery id="1,2,3,6,11,20"]
```

## Hooks

If something cannot be described statically, VersionPress offers several filters, actions and functions to define behavior through code. Implement them in the `hooks.php` file.

Most of the filters have already been discussed in the text above, you can find the full API reference below.


## Ignored folders

Feel free to use custom `.gitignore` for files in the plugin directory. You can also ignore files / directories outside the plugin directory. There will be a filter to let VersionPress know which files / directories you want to ignore.


## Discovery mechanism

VersionPress looks for plugin definitions in these locations, in this order:

1. `WP_CONTENT_DIR/.versionpress/<plugin-slug>`
2. `WP_PLUGIN_DIR/<plugin-slug>/.versionpress`

The first definition found is used.

## Resources

- Issue [#1036](https://github.com/versionpress/versionpress/issues/1036) – everything was discussed there.

## API reference

TODO this will be auto-generated from code.

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
 - `vp_action_priority_{$scope}`
   - `apply_filters("vp_action_priority_{$entityName}", $defaultPriority, $action, $vpid, $entity)`
   - `apply_filters("vp_action_priority_{$entityName}", $defaultPriority, $action, $vpid)`

### Actions

 - `vp_before_synchronization_{$entityName}`
   - `do_action("vp_before_synchronization_{$entityName}")`
 - `vp_after_synchronization_{$entityName}`
   - `do_action("vp_after_synchronization_{$entityName}")`

### Functions

 - `vp_force_action`
   - `vp_force_action($scope, $action, $id = '', $tags = [], $files = [])`

