<?php

class UsersSynchronizer extends SynchronizerBase {
    function __construct(Storage $storage, wpdb $database, DbSchemaInfo $dbSchema) {
        parent::__construct($storage, $database, $dbSchema, 'users');
    }

    protected function filterEntities($entities) {
        static $allowedProperties = array(
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

        $filteredEntities = array();
        foreach($entities as $entity){
            $safeEntity = array();
            foreach($allowedProperties as $allowedProperty){
                if(isset($entity[$allowedProperty])){
                    $safeEntity[$allowedProperty] = $entity[$allowedProperty];
                }
            }
            $filteredEntities[] = $safeEntity;
        }

        return parent::filterEntities($filteredEntities);
    }
}