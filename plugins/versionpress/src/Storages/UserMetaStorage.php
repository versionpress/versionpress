<?php

namespace VersionPress\Storages;

use Nette\Utils\Strings;
use VersionPress\ChangeInfos\UserMetaChangeInfo;

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
            $this->save($entity);
        }
    }

    protected function createChangeInfo($oldEntity, $newEntity, $action = null) {
        return new UserMetaChangeInfo($action, $this->userMetaVpId, $newEntity['user_login'], $this->userMetaKey);
    }

    public function shouldBeSaved($data) {
        if ($this->keyEquals($data, 'session_tokens') ||
            $this->keyEquals($data, 'nav_menu_recently_edited')) {
            return false;
        }

        if ($this->keyEndsWith($data, 'dashboard_quick_press_last_post_id')) {
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

    private function keyEquals($data, $key) {
        return (isset($data['meta_key']) && $data['meta_key'] === $key) || $this->userMetaKey === $key;
    }

    private function keyEndsWith($data, $suffix) {
        return (isset($data['meta_key']) && Strings::endsWith($data['meta_key'], $suffix)) || Strings::endsWith($this->userMetaKey, $suffix);
    }
}
