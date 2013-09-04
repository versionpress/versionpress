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
        $propertiesBlacklist = array(
            'ID',
            'user_login',
            'user_pass',
            'user_nicename',
            'user_email',
            'user_url',
            'user_registered',
            'user_activation_key',
            'user_status',
            'display_name',
            'vp_id'
        );

        $allMetaVpIdsQuery = sprintf('select HEX(reference_vp_id) as user_vp_id, HEX(vp_id) as meta_vp_id, meta_key, id as meta_id ' .
            'from %s ' .
            'join %s on id = umeta_id ' .
            'where `table` = "usermeta"',
            $this->dbSchema->getPrefixedTableName('vp_reference_details'),
            $this->dbSchema->getPrefixedTableName('usermeta'));

        $metaIdsSource = $this->database->get_results($allMetaVpIdsQuery);
        $metaIdsMap = $this->createMetaIdsMap($metaIdsSource);

        $transformedEntities = array();
        foreach ($entities as $entity) {
            foreach($entity as $meta_key => $meta_value) {
                if(in_array($meta_key, $propertiesBlacklist))
                    continue;
                $transformedEntity = array();
                $transformedEntity['vp_id'] = $metaIdsMap[$entity['vp_id']][$meta_key]['meta_vp_id'];
                $transformedEntity['umeta_id'] = $metaIdsMap[$entity['vp_id']][$meta_key]['umeta_id'];
                $transformedEntity['meta_key'] = $meta_key;
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