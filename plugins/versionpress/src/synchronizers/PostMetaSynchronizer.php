<?php

class PostMetaSynchronizer extends SynchronizerBase {

    /** @var wpdb */
    private $database;

    /** @var  DbSchemaInfo */
    private $dbSchema;

    function __construct(Storage $storage, wpdb $database, DbSchemaInfo $dbSchema) {
        parent::__construct($storage, $database, $dbSchema, 'postmeta');
        $this->database = $database;
        $this->dbSchema = $dbSchema;
    }

    protected function transformEntities($entities) {
        $transformedEntities = array();
        foreach ($entities as $postId => $entity) {
            foreach ($entity as $meta_key => $meta_value) {
                $dividerPosition = strrpos($meta_key, '#');

                if ($dividerPosition === false)
                    continue;

                $key = substr($meta_key, 0, $dividerPosition);
                $id = substr($meta_key, $dividerPosition + 1);


                $transformedEntity = array();
                $transformedEntity['vp_id'] = $id;
                $transformedEntity['vp_post_id'] = $postId;
                $transformedEntity['meta_key'] = $key;
                $transformedEntity['meta_value'] = $meta_value;
                $transformedEntities[] = $transformedEntity;
            }
        }

        return parent::transformEntities($transformedEntities);
    }
}
