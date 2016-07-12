# Plugin Support

> :construction: UNDER CONSTRUCTION. This page gathers ideas, examples and technical details about how VersionPress is going to support 3rd-party plugins and themes. **It's not stable or fully implemented as long as this warning is here**.

VersionPress needs to understand plugins' data and actions in order to provide version control for them. This page details the interface that VersionPress provides to 3rd-party plugin authors to integrate with it.

> Note: *Themes* are technically similar but less often create custom data structures so this page only describes plugins to keep things simpler.  


## Motivation

For a WordPress site, VersionPress provides these three basic things when it come to versioning:

1. It tracks its **actions**.
2. It **describes those actions** in a user-friendly way – something like *"Created post xyz"* rather than *"Inserted DB row"*.
3. It **understands database schema** – especially, what identifies entities, what are the references between them, which entities should be ignored, etc.

Plus, there are a couple of other things like shortcodes etc. But the above is core and VersionPress generally needs to understand plugins similarly well. One of the trickiest problems is database entities and references between them. Even something simple like a plugin storing an option with a value of `123` is challenging: is that a price of something? A reference to a post ID `132`? Some other entity? VersionPress needs to know in order to update these numbers when merging between environments, among other things.


## Plugin descriptions

To be fully supported, plugins must be described for VersionPress. This includes:

- Actions
- Data model (see `wordpress-schema.yml`)
- Shortcodes (see `wordpress-shortcodes.yml`)
- Ignored folders

### Data model (schema)

To make VersionPress work properly it needs to understand the database schema of the plugin. It needs to know what tables the plugin uses, which columns contain the primary keys and what relations are between the tables.

To define the data model you have to create a "schema file" and provide it with your plugin (see section [Discovery mechanism](#discovery-mechanism) for more). You can find more about the schema file in the [schema readme](../versionpress/plugins/src/Database/schema-readme.md).


### Actions

Every logical change of an entity is represented by an action. When you change a site title the action will be `option/edit/blogname`. This action defines which message will be used for a commit message.

To define the actions you have to create an "action file" and provide it with your plugin.

Example of action file:

    post:
      tags:
        post-title: post_title
        post-type: post_type
      actions:
        create: Created %post-type% '%post-title%'
        edit: Edited %post-type% '%post-title%'
        trash:
          message: %post-type% '%post-title%' moved to trash
          priority: 7

Every entity has two sections: `tags` and `actions`. Tags are values from the entity saved within the commit. You can use them from messages. The "actions" section contains all actions related to the entity and messages that will be displayed from them. Optionally, you can specify the priority. Default value is 10 (it works like filters / hooks).

> Note: We also consider relative priorities like `post/trash` has higher priority than `post/edit` etc. but it's far more difficult.

VersionPress detects only three basic actions – `create`, `edit` and `delete`. For more specific action you can use a filter (`vp_entity_action_{$entityName}`) which has three parameters – original action, entity in a state before the action and entity in a state after the action.

The tags are automatically extracted from the entity. The action file contains pairs of tag's name and the corresponding field. The new state has a higher priority than the old one. Also, you can alter the tags in a filter (`vp_entity_tags_{$entityName}`) which takes four parameters – original tags, entity in a state before the action, entity in a state after the action and the action.

To every action relates a possible modification of files. For example when you edit a post, an action `post/edit` will occure. This action also says that an INI file containing this post should be committed. If the post represents an uploaded file, it also says that some files in uploads directory could change and should be committed as well.

Similarly to the action and tags, you can modify also the list of committed files using a filter (`vp_entity_files_{$entityName}`). The callback takes a list of possibly changed files, entity in a state before the action and entity in a state after the action.

The list can contain three different types of items:

1) Single file corresponding an entity
 - `[ 'type' => 'storage-file', 'entity' => 'entity name',
    'id' => 'VPID', 'parent-id' => 'VPID of parent (for meta entities)']`

2) All files in a specific repository
 - `[ 'type' => 'all-storage-files', 'entity' => 'entity name']`

3) Path on the filesystem
- `[ 'type' => 'path', 'path' => 'some/path/with/wildcards/*']`

---

For actions that are not related to DB entities (e.g. manipulating with plugins / themes) you can also use a filter (TODO).

### Shortcodes

VersionPress also needs to know your shortcodes if they reference DB entities. Similar to the data model, you have to provide a file containing definitions of shortcodes. You can find more about the file in the [shortcodes readme](../versionpress/plugins/src/Database/shortcodes-readme.md).

### Ignored folders

Feel free to use custom `.gitignore` for files in the plugin directory. You can also ignore files / directories outside the plugin directory. There is a filter to let VersionPress know which files / directories you want to ignore (TODO).


## Discovery mechanism

TODO


## References

- Issue [#1036](https://github.com/versionpress/versionpress/issues/1036) – everything was discussed there.
