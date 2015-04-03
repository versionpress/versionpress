<?php

namespace VersionPress\Tests\End2End\Utils;

interface IPostTypeTestWorker extends ITestWorker {

    public function prepare_addPost();
    public function addPost();

    public function prepare_updatePost();
    public function updatePost();

    public function prepare_quickEditPost();
    public function quickEditPost();

    public function prepare_trashPost();
    public function trashPost();

    public function prepare_untrashPost();
    public function untrashPost();

    public function prepare_deletePost();
    public function deletePost();

    public function prepare_createDraft();
    public function createDraft();

    public function prepare_previewDraft();
    public function previewDraft();
    public function cleanup_previewDraft();


    public function prepare_publishDraft();
    public function publishDraft();

    public function getPostType();

    public function prepare_previewUnsavedPost();
    public function previewUnsavedPost();

    public function prepare_createTagInEditationForm();
    public function createTagInEditationForm();

    public function prepare_setFeaturedImageForUnsavedPost();
    public function setFeaturedImageForUnsavedPost();

    public function prepare_makeDraftFromUnsavedPost();
    public function makeDraftFromUnsavedPost();

}
