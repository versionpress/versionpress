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

To define the action you have to create an "action file" and provide it with your plugin.

### Shortcodes

VersionPress also needs to know your shortcodes if they reference DB entities. Similar to the data model, you have to provide a file containing definitions of shortcodes. You can find more about the file in the [shortcodes readme](../versionpress/plugins/src/Database/shortcodes-readme.md).

### Ignored folders

TODO


## Discovery mechanism

TODO


## References

- Issue [#1036](https://github.com/versionpress/versionpress/issues/1036) – everything was discussed there.
