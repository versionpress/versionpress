#!/bin/bash

# Repo merge script used to merge versionpress/docs into versionpress/versionpress.
# Based on https://gist.github.com/borekb/eccc0af01f3b2c1d47bb7955a08bc214 and 
# https://gofore.com/merge-multiple-git-repositories-one-retaining-history/ 

# How to use:
# 
# 1. Create a temp directory
# 2. Checkout `versionpress` and `docs` there (these names exactly)
# 3. Put this script into the temp directory, side by side with `versionpress` and `docs`, and run it.

#---------------------------------
# Variables
#---------------------------------

TARGET_REPO='versionpress' # should be a folder relative to this script
CHILD_REPO='docs' # should be a folder relative to this script
NEW_PATH='docs' # a subfolder in the target repo, `-` must be escaped as `\-`
BRANCH_TO_USE='1113-merge-docs-repo'
TARGET_REPO_ISSUE='1113'

RUN_CHILD_REPO_PROCESSING=true
RUN_REPO_MERGE=true


#---------------------------------
# Prepare the child repo
#---------------------------------

if [ "$RUN_CHILD_REPO_PROCESSING" = true ]
then

cd $CHILD_REPO

# Create local branches
remote=origin ; for brname in `git branch -r | grep $remote | grep -v master | grep -v HEAD | awk '{gsub(/^[^\/]+\//,"",$1); print $1}'`; do git branch $brname $remote/$brname; done

# Remove remote branches (`/remotes/origin/*` refs)
git remote remove origin

# Remove all tags
git tag | xargs git tag -d

# Filter out `content/media`
git filter-branch --tag-name-filter cat --index-filter 'git rm -r --cached --ignore-unmatch content/media' --prune-empty -f -- --all

# Filter paths so it looks like the commits always went to a path in the target repo.
git filter-branch --index-filter \
    'git ls-files -s | sed "s-\t\"*-&'$NEW_PATH'/-" |
    GIT_INDEX_FILE=$GIT_INDEX_FILE.new \
    git update-index --index-info &&
    mv "$GIT_INDEX_FILE.new" "$GIT_INDEX_FILE"
    ' --tag-name-filter cat -f -- --all

# Replace `#123` with `versionpress/docs#123`
#   Test with: echo '#1 [#123] (#123) issue #2 other/repo#12 [other/repo#12]' | sed -r 's/(\W|^)#([0-9]+)/\1versionpress\/docs#\2/g'
git filter-branch --tag-name-filter cat --msg-filter 'sed -r '\''s/(\W|^)#([0-9]+)/\1versionpress\/docs#\2/g'\''' -f -- --all

# Replace `WP-123` with `versionpress/versionpress#123`
#   Test with: echo 'WP-123 [WP-123] (WP-123) nonWP-999' | sed -r 's/(\W|^)WP\-([0-9]+)/\1versionpress\/versionpress#\2/g'
git filter-branch --tag-name-filter cat --msg-filter 'sed -r '\''s/(\W|^)WP\-([0-9]+)/\1versionpress\/versionpress#\2/g'\''' -f -- --all

# Clean up the `original` refs that Git keeps as a backup
git for-each-ref --format="%(refname)" refs/original/ | xargs -n 1 git update-ref -d

cd ..

fi

#---------------------------------
# Merge the repos
#---------------------------------

if [ "$RUN_REPO_MERGE" = true ]
then

cd $TARGET_REPO
git checkout -b $BRANCH_TO_USE
git remote add -f $CHILD_REPO ../$CHILD_REPO
git merge --allow-unrelated-histories -m "[#$TARGET_REPO_ISSUE] Merge $CHILD_REPO repo" $CHILD_REPO/master
git remote rm $CHILD_REPO

fi

#---------------------------------
# Push
#---------------------------------

# After manually inspecting:
# git push origin $BRANCH_TO_USE
