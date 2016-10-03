# Plugin Support

> :construction: The plugin support is **not stable or fully implemented as long as this warning is here**.

VersionPress needs to understand plugins' data, actions, shortcodes and other things to automatically provide version control for them. If you're a plugin developer or enthusiast, this document is for you.

> Note that *themes* are technically similar and will be supported in pretty much the same way, however, we focus on plugins first.


## Introduction

Plugins are described to VersionPress by a set of files stored in the `.versionpress` folder in the plugin root (other discovery options are described below). They include:

- Actions in `actions.yml`
- Database schema in `schema.yml`
- Shortcodes in `shortcodes.yml`
- Hooks are defined in `hooks.php`

All files are optional so for example, if a plugin doesn't define any new shortcodes it can omit the `shortcodes.yml` file. Simple plugins like _Hello Dolly_ might even omit everything; they just need to have the `.versionpress` folder so that VersionPress knows the plugin is supported.

By the way, WordPress itself is described to VersionPress as a set of these files (it's the ultimate test of the format because WordPress sometimes does crazy things!). You can take a look at [the source files](../plugins/versionpress/.versionpress) to draw inspiration from there.


## Actions

Actions represent what the plugin does. For example, WooCommerce might have actions like "update product", "create invoice" etc. Actions are described in the `actions.yml` file and eventually stored in commit messages to describe to users what happened in their site.

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

A commit can even contain more actions, for example, if two options are updated together, the commit may look like this:

```
Edited 2 options

VP-Action: option/edit/blogdescription

VP-Action: option/edit/blogname
```

This brief excurse will help you understand a couple of things about the `actions.yml` format.

### `actions.yml` format

An example from describing the core WordPress actions:

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

You can see these elements in action:

- The top-level elements define **scopes** that basically group the actions. For example, actions related to WordPress posts are in the `post` scope, theme actions are in the `theme` scope, etc. 
- **Tags** are values saved in the commit message and are typically used to make the eventual UI messages more useful. For example, it's better to display _Created post 'Hello World'_ than _Created post ID 123_.
- **The `actions` section** contains all actions under the scope. A combination of scope and action like `post/create` or `theme/install` is a "full action" that can be searched for in the UI and generally uniquely identifies the action.
- An action has a **message** (usually in past tense) and a **priority**. If priority is not present, the default value of 10 is used.
    - Priorities behave like those on WordPress filters and actions: the lower the number, the higher the priority. A more important action beats the less important one if both appear in the same commit. For example, `theme/switch` beats `option/edit` which means that the user will see a message about changing themes, not updating some internal option.
- **Meta entities** also contain **`parent-id-tag`** with the name of a tag containing ID of the parent entity.


### Action detection

You might be asking how VersionPress assigns these actions to real changes in a WordPress site. It works like this:

VersionPress **automatically** detects **three basic actions** – `create`, `edit` and `delete`, based on the SQL query issued. You don't need to do anything if you need just these three basic actions.

For more **specific actions** like `post/trash` or `comment/approve`, filters are used:

- For standard entities, use the `vp_entity_action_{$entityName}` filter which has three parameters: original action, entity in a state before the action and the same entity in a state after the action.
- For meta entities, use the `vp_meta_entity_action_{$entityName}` filter with five parameters: the two extra are parent entity in a state before and after the action.

**Tags** are automatically extracted from the entity. For example, this snippet:

```yaml
  tags:
    VP-Post-Title: post_title
```

makes sure that the `VP-Post-Title` tag contains the value from the `post_title` database field.

You can alter tags in a filter `vp_entity_tags_{$entityName}` which takes four parameters: the original tags, entity in a state before the action, entity in a state after the action and the action. Similarly to before, use `vp_meta_entity_tags_{$entityName}` for meta entities with two extra parameters representing parent entity and its states.

### Files to commit with an action

So far we've only talked about action descriptions that are stored in commit _messages_ but what about the actual content?

For database changes, VersionPress automatically commits the corresponding INI file. For example, for a `post/edit` action, a post's INI file is committed.

> Side note: VersionPress stores database entities in the `wp-content/vpdb` folder as a set of INI files.

This behavior is sufficient most of the time, however, some changes should commit more files. For example, when the post is an attachment, the uploaded file should also be committed. For this, the list of files to commit can be filtered using `vp_entity_files_{$entityName}` or `vp_meta_entity_files_{$entityName}`. The callback takes a list of possibly changed files, the entity in a state before the action, the entity in a state after the action and in case of meta entity, also the parent entity in states before and after the action.

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

### Non-database actions

Some actions are not directly related to the database entities, e.g. plugin installation, WP update, etc. VersionPress provides function `vp_force_action` for these actions. VersionPress will use only action specified by parameters of this function and ignore all automatically catched. For example:

```
vp_force_action('wordpress', 'update', $version, [], $wpFiles);
```

> Warning: This function is only a temporary solution and will be removed in 4.0.

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

VersionPress needs to understands relationships between entities so that it can update their IDs between environments. There are several types of references, each using a slightly different notation in the schema file.

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

The complete syntax is:

```yaml
value-references:
  <source_column_name>@<value_column_name>:
    <source_column_value>: <foreign_entity_name | @mapping_function>
    <source_column_value>["path-in-serialized-objects"][/\d+/][0]: <foreign_entity_name | @mapping_function>
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

Feel free to use custom `.gitignore` for files in the plugin directory. You can also ignore files / directories outside the plugin directory. There is a filter to let VersionPress know which files / directories you want to ignore (TODO).


## Discovery mechanism

VersionPress looks for plugin descriptors in these locations, in this order:

1. VersionPress installation folder
2. `.versionpress` folder in plugin root
3. Online repository

We only plan to use **(1)** for WordPress core, plugins will typically use **(2)** or **(3)**.

Option **(2)** is good if the plugin authors want to support VersionPress directly (and hopefully more and more will).

Option **(3)** is a fallback method but a good one: anyone can create a definition files for their favorite plugins and share them via an online repository. We're working on it.


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

### Actions

 - `vp_before_synchronization_{$entityName}`
   - `do_action("vp_before_synchronization_{$entityName}")`
 - `vp_after_synchronization_{$entityName}`
   - `do_action("vp_after_synchronization_{$entityName}")`

### Functions

 - `vp_force_action`
   - `vp_force_action($scope, $action, $id = '', $tags = [], $files = [])`

