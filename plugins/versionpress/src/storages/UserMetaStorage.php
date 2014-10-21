<?php

class UserMetaStorage extends SingleFileStorage implements EntityStorage {

    private $userMetaKey;
    private $userMetaVpId;

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

        $this->userMetaKey = $values['meta_key'];
        $this->userMetaVpId = $values['vp_id'];

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
     * @param $entity
     * @param $changeType
     * @return EntityChangeInfo
     */
    protected function createChangeInfo($entity, $changeType) {
        return new UserMetaChangeInfo($changeType, $this->userMetaVpId, $entity['user_login'], $this->userMetaKey);
    }
}