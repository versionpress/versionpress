<?php

class UserMetaStorage extends SingleFileStorage implements EntityStorage {

    function __construct($file) {
        parent::__construct($file, 'user', 'ID');
    }

    function save($data, $restriction = array(), $id = 0) {
        $values = array_merge($data, $restriction);
        $key = sprintf('%s#%s', $values['meta_key'], $values['vp_id']);
        $data = array(
            'vp_id' => $values['vp_user_id'],
            $key => $values['meta_value']
        );

        $this->saveEntity($data, array($this, 'notifyOnChangeListeners'));
    }

    function saveAll($entities) {
        foreach($entities as $entity) {
            $key = sprintf('%s#%s', $entity['meta_key'], $entity['vp_id']);
            $data = array(
                'vp_id' => $entity['vp_user_id'],
                $key => $entity['meta_value']
            );

            $this->saveEntity($data);
        }
    }

    /**
     * @param $entityId
     * @param $changeType
     * @return EntityChangeInfo
     */
    protected function createChangeInfo($entityId, $changeType) {
        // TODO: Implement createChangeInfo() method.
    }
}