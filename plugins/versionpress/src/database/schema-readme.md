# NEON Schema Format #

Some essential information about database entities and their relationships is described in a [NEON](http://ne-on.org/) format which is then parsed and made accessible via the `DbSchemaInfo` class. This excerpt from `wordpress-schema.neon` is basically a complete example of all the possible options: 

    posts:
        id: ID
        references:
            post_author: users
            post_parent: posts

    users:
        id: ID

    options:
        vpid: option_name


## Defining entities

The top-level keys are **entity names** (`posts`, `users` and `options` in this example). Entity names **match database table names** without a prefix and there is a record in the schema file for every database table that is being tracked. (There may be database tables that VersionPress doesn't care about and those are not in the schema; that's fine.)


## Identifying entities

VersionPress needs to know how to identify entities. There are two approaches, and they are designated by either using `id` or `vpid` in the schema:

 * **id** points to a standard WordPress auto-increment primary key. **VersionPress will generate VPIDs** for such entities because simple numeric ID is generally not enough to uniquely identify an entity across multiple environments (would cause conflicts). Most entities are of this type – posts, comments, users etc.

 * **vpid**, unlike `id`, directly points VersionPress to use the given column as a unique identifier and skip the whole VPID generation and maintenance process. So entities of this type **will not have an artificial VPID** – they will have a natural one. The `options` table is an example of this – even though it has an `option_id` auto-increment primary key, from VersionPress's point of view the unique identifier is `option_name`.


## References

WordPress db schema doesn't store foreign keys so we need to. An entity can have zero to n references to other entities, in which case it uses the format

    references:
        <my_column_name>: <foreign_entity_name>

VersionPress knows what ID to be looking for in the foreign entity name because it is also described somewhere in the schema. 