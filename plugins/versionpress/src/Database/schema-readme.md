# YAML Schema Format #

Some essential information about database entities and their relationships is described in a [YAML](http://yaml.org/) format which is then parsed and made accessible via the `VersionPress\Database\DbSchemaInfo` class. This excerpt from `wordpress-schema.yml` is basically a complete example of all the possible options:

    post:
        table: posts
        id: ID
        references:
            post_author: user
            post_parent: post
        mn-references:
            term_relationships.term_taxonomy_id: term_taxonomy

    postmeta:
        id: meta_id
        parent-reference: post_id
        references:
            post_id: post
        value-references:
            meta_key@meta_value:
                _thumbnail_id: post
                _menu_item_object_id: '@\VersionPress\Database\VpidRepository::getMenuReference'

    user:
        table: posts
        id: ID

    option:
        table: options
        vpid: option_name
        frequently-written:
            - 'option_name: akismet_spam_count'
            - query: 'option_name: request_counter'
              interval: 5min
        ignored-entities:
            - 'option_name: _*'
            - 'option_name: siteurl'

## Defining entities

The top-level keys are **entity names** (`post`, `user`, `usermeta` and `option` in this example). By default, the entity names **match database table names** without a prefix, however it is possible to specify different table name (e.g. post > posts). Also there is a record in the schema file for every database table that is being tracked. (There may be database tables that VersionPress doesn't care about and those are not in the schema; that's fine.)


## Identifying entities

VersionPress needs to know how to identify entities. There are two approaches, and they are designated by either using `id` or `vpid` in the schema:

 * **id** points to a standard WordPress auto-increment primary key. **VersionPress will generate VPIDs** for such entities because simple numeric ID is generally not enough to uniquely identify an entity across multiple environments (would cause conflicts). Most entities are of this type – posts, comments, users etc.

 * **vpid**, unlike `id`, directly points VersionPress to use the given column as a unique identifier and skip the whole VPID generation and maintenance process. So entities of this type **will not have an artificial VPID** – they will have a natural one. The `options` table is an example of this – even though it has an `option_id` auto-increment primary key, from VersionPress's point of view the unique identifier is `option_name`.


## References

WordPress db schema doesn't store foreign keys so we need to. An entity can have zero to n references to other entities, in which case it uses the format

    references:
        <my_column_name>: <foreign_entity_name>

VersionPress knows what ID to be looking for in the foreign entity name because it is also described somewhere in the schema.

When the reference to entity depends on another column value, use value-reference. It is neccessary to specify column where is the dependency (source column), 
column where is the foreign id itself (value column) and the name of foreign entity. Instead of the static name of entity it is possible to use dynamic mapping.
For the mapping can be used either a function or static method. Mapping function or method are prefixed with `@`.
 
If the ID is in a serialized object, you can specify the path by a suffix of the source column. It looks like array access but also supports regular expressions - see the sample below.

Value references also supports wildcards in the name of source column. It's useful e.g. for options named `theme_mods_{name of the theme}`.

    value-references:
        <source_column_name>@<value_column_name>:
            <source_column_value>: <foreign_entity_name | @mapping_function>
            <source_column_value>["path-in-serialized-objects"][/\d+/][0]: <foreign_entity_name | @mapping_function>
            <columns_with_prefix_*>: <foreign_entity_name | @mapping_function>

Another type of references are the M:N references. Sometimes (for example between posts and term_taxonomies) we need to describe
an M:N relationship (junction table in the SQL). To do that we can use this format

    mn-references:
        <junction_table_name>.<column_name>: <foreign_entity_name>
        ~<junction_table_name>.<column_name>: <foreign_entity_name>

As you can see, the reference can be prefixed with a tilde (~). It means that the reference is virtual - the entity does not contain
the data but is's checked in Reverter. The reference is usually saved within the foreign entity (e.g. the `post` contains a list of `term_taxonomy` VPIDs
but the `term_taxonomy` does NOT contain a list of `post` VPIDs).

Some entities are saved within other entities (e.g. the `postmeta` are saved in the same .ini file with `post` they belong to). As part of this we introduced
concept of “parent entities”.

    postmeta:
        id: meta_id
        parent-reference: post_id
 
 The field `parent-reference` contains the name of one of the simple references. This specifies within which entity will be the child entity saved.

## Frequently written entities

Some entities are changed very often (view counter, akismet spam count, etc.). It is possible to save them once in a while.
They are specified in section `frequently-written`. It's a list of selectors or combination of selector and custom interval. Default interval is `1hour`.

    frequently-written:
        - 'column_name: value'
        - query: 'column1_name: value1 column2_name: value2'
          interval: 5min

The interval is parsed by PHP function `strtotime`, so it can be whatever the function takes.

In case of clone/staging setup don't set the interval too short (below `1min`). The shorter the interval is, the more likely is the merge conflict.

## Ignoring entities

It is possible to ignore some entities (don't save them into INI files). You can just write some queries identifying those entities.

    ignored-entities:
        - 'option_name: _*'
        - 'option_name: siteurl'
