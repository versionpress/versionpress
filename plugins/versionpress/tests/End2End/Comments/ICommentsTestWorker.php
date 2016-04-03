<?php

namespace VersionPress\Tests\End2End\Comments;

interface ICommentsTestWorker {
    public function prepare_createCommentAwaitingModeration();
    public function createCommentAwaitingModeration();

    public function prepare_createComment();
    public function createComment();

    public function prepare_editComment();
    public function editComment();

    public function prepare_trashComment();
    public function trashComment();

    public function prepare_untrashComment();
    public function untrashComment();

    public function prepare_deleteComment();
    public function deleteComment();

    public function prepare_unapproveComment();
    public function unapproveComment();

    public function prepare_approveComment();
    public function approveComment();

    public function prepare_markAsSpam();
    public function markAsSpam();

    public function prepare_markAsNotSpam();
    public function markAsNotSpam();

    public function prepare_editTwoComments();
    public function editTwoComments();

    public function prepare_deleteTwoComments();
    public function deleteTwoComments();

    public function prepare_moveTwoCommentsInTrash();
    public function moveTwoCommentsInTrash();

    public function prepare_moveTwoCommentsFromTrash();
    public function moveTwoCommentsFromTrash();

    public function prepare_markTwoCommentsAsSpam();
    public function markTwoCommentsAsSpam();

    public function prepare_markTwoSpamCommentsAsNotSpam();
    public function markTwoSpamCommentsAsNotSpam();

    public function prepare_unapproveTwoComments();
    public function unapproveTwoComments();

    public function prepare_approveTwoComments();
    public function approveTwoComments();
    
    public function prepare_commentmetaCreate();
    public function commentmetaCreate();

    public function prepare_commentmetaDelete();
    public function commentmetaDelete();
}
