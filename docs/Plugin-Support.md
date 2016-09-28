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

TODO: inline [schema readme](../plugins/versionpress/.versionpress/schema-readme.md) here.



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
