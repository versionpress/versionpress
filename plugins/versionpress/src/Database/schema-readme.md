# Database Schema Format

VersionPress needs to understand database entities and their relationships in order to track them properly. WordPress does not provide enough information about this so VersionPress depends on a set of YAML files, so called "schema files", that define everything important about the database entities.

> Note: Currently, there is just `wordpress-schema.yml` describing the base WordPress structure. In the future, plugins and themes will be able to provide their own schemata and the system will be extensible. 

For example, this is how posts are described:

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
    - comment_count: '@\VersionPress\Synchronizers\PostsSynchronizer::fixCommentCounts'
```


## Defining entities

The top-level keys in YAML files define entity names such as `post`, `comment`, `user`, `option` or `postmeta`. Entity names use a singular form.

By default, the entity names match database table names without the `wp_` (or custom) prefix. It is possible to specify a different table name using the `table` property:

```
post:
  table: posts
  ...
```

Again, this is prefix-less; something like `wp_` will be added automatically.


## Identifying entities

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

## References

VersionPress needs to understands relationships between entities so that it can update their IDs between environments. There are several types of references, each using a slightly different notation in the schema file.


### Basic references

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


### Value references

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


### M:N references

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


### Parent references

Some entities are stored within other entities, for example, postmeta are stored in the same INI file as their parent post. This is captured using a `parent-reference` property:

```
postmeta:
  parent-reference: post_id
  references:
    post_id: post

```

This references one of the basic reference column names, not the final entity. The notation above reads "postmeta stores a parent reference in the `post_id` column and that points to the `post` entity".


## Frequently written entities

Some entities are changed very often, e.g., view counters, Akismet spam count, etc. VersionPress only saves them once in a while and the `frequently-written` section influences this:

```
entity:
  frequently-written:
    - 'column_name: value'
    - query: 'column1_name: value1 column2_name: value2'
      interval: 5min
```

The values in the `frequently-written` array can either be strings which are then interpreted as queries, or objects with `query` and `interval` keys. Queries use the same syntax as search / filtering in the UI, with some small differences like that the date range operator cannot be used but overall, the syntax is pretty intuitive. The interval is parsed by the `strtotime()` PHP function and the default value is one hour.


## Ignoring entities

Some entities should be ignored, i.e., not tracked at all, like transient options, environment-specific things etc. This is an example from the `option` entity:

```
  ignored-entities:
    - 'option_name: _transient_*'
    - 'option_name: _site_transient_*'
    - 'option_name: siteurl'
```

Again, queries are used. Wildcards are supported.


### Ignoring columns

It is possible to ignore just parts of entities – their columns. The columns might either be ignored entirely or computed dynamically using a PHP function:

```
entity:
  ignored-columns:
    - column_name_1
    - column_name_2
    - computed_column_name: '@functionReference'
```

The function is called whenever VersionPress does its INI files => DB synchronization. The function will get an instance of `VersionPress\Database\Database` as an argument and is expected to update the database appropriately.
