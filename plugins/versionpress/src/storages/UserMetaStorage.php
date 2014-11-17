<?php

class UserMetaStorage extends SingleFileStorage {

    private $userMetaKey;
    private $userMetaVpId;

    function __construct($file) {
        parent::__construct($file, 'user', 'ID');
    }

    function save($values) {
        $data = $this->transformToUserField($values);

        $this->userMetaKey = $values['meta_key'];
        $this->userMetaVpId = $values['vp_id'];

        $this->saveEntity($data, array($this, 'notifyOnChangeListeners'));
    }

    function saveAll($entities) {
        foreach($entities as $entity) {
            $data = $this->transformToUserField($entity);
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

    public function shouldBeSaved($data) {
        if(isset($data['meta_key']) && $data['meta_key'] === 'session_tokens') return false;
        if(NStrings::startsWith(key($data), 'session_tokens')) return false;

        return parent::shouldBeSaved($data);
    }

    private function transformToUserField($values) {
        $key = sprintf('%s#%s', $values['meta_key'], $values['vp_id']);
        $data = array(
            'vp_id' => $values['vp_user_id'],
            $key => $values['meta_value']
        );
        return $data;
    }
}