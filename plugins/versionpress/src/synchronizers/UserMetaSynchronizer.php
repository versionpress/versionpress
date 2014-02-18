<?php

class UserMetaSynchronizer extends SynchronizerBase {
    /** @var  wpdb */
    private $database;

    /** @var  DbSchemaInfo */
    private $dbSchema;

    function __construct(EntityStorage $storage, wpdb $database, DbSchemaInfo $dbSchema) {
        parent::__construct($storage, $database, $dbSchema, 'usermeta');
        $this->database = $database;
        $this->dbSchema = $dbSchema;
    }

    protected function transformEntities($entities) {
        $transformedEntities = array();
        foreach ($entities as $entity) {
            foreach($entity as $meta_key => $meta_value) {
                $dividerPosition = strrpos($meta_key, '#');

                if($dividerPosition === false)
                    continue;

                $key = substr($meta_key, 0, $dividerPosition);
                $id = substr($meta_key, $dividerPosition + 1);


                $transformedEntity = array();
                $transformedEntity['vp_id'] = $id;
                $transformedEntity['meta_key'] = $key;
                $transformedEntity['meta_value'] = $meta_value;
                $transformedEntities[] = $transformedEntity;
            }
        }

        return parent::transformEntities($transformedEntities);
    }

    /**
     * Transforms data from database in format
     * [{user_vp_id: '123...', meta_vp_id: '321...', meta_key: 'some_key}, {...}]
     * to map
     * [$user_vp_id => [$meta_key => ['meta_vp_id' => $meta_vp_id, 'umeta_id' => $meta_id]]
     * @param $metaIdsSource
     * @return array
     */
    private function createMetaIdsMap($metaIdsSource) {
        $metaIdsMap = array();

        foreach ($metaIdsSource as $metaIdSource) {
            $metaIdsMap[$metaIdSource->user_vp_id][$metaIdSource->meta_key] =
                array('meta_vp_id' => $metaIdSource->meta_vp_id, 'umeta_id' => intval($metaIdSource->meta_id));
        }
        return $metaIdsMap;
    }
}