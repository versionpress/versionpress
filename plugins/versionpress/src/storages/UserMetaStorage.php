<?php

class UserMetaStorage extends SingleFileStorage {

    private $userMetaKey;
    private $userMetaVpId;

    function save($data) {
        $transformedData = $this->transformToUserField($data);

        $this->userMetaKey = $data['meta_key'];
        $this->userMetaVpId = $data['vp_id'];

        return parent::save($transformedData);
    }

    function saveAll($entities) {
        foreach ($entities as $entity) {
            $data = $this->transformToUserField($entity);
            parent::save($data);
        }
    }

    protected function createChangeInfo($oldEntity, $newEntity, $action = null) {
        return new UserMetaChangeInfo($action, $this->userMetaVpId, $newEntity['user_login'], $this->userMetaKey);
    }

    public function shouldBeSaved($data) {
        if ($this->userMetaKey === 'session_tokens') {
            return false;
        }

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