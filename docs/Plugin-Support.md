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

### Actions

TODO

### Data model (schema)

TODO

### Shortcodes

TODO

### Ignored folders

TODO


## Discovery mechanism

TODO


## References

- Issue [#1036](https://github.com/versionpress/versionpress/issues/1036) – everything was discussed there.