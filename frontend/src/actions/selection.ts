/// <reference path='../components/common/Commits.d.ts' />

import { indexOf } from '../utils/CommitUtils';
import { appStore, commitsTableStore } from '../stores';

export function selectCommits(commitsToSelect: Commit[], isChecked: boolean, isShiftKey: boolean) {
  const { commits } = commitsTableStore;
  let { lastSelectedCommit } = appStore;
  const isBulk = commitsToSelect.length > 1;

  let selectedCommits = appStore.selectedCommits.slice(0);

  commitsToSelect
    .filter(commit => commit.canUndo)
    .forEach(commit => {
      let lastIndex = -1;
      const index = indexOf(commits, commit);

      if (!isBulk && isShiftKey && lastSelectedCommit) {
        lastIndex = indexOf(commits, lastSelectedCommit);
      }

      lastIndex = lastIndex === -1 ? index : lastIndex;

      const step = index < lastIndex ? -1 : 1;
      const cond = index + step;
      for (let i = lastIndex; i !== cond; i += step) {
        const currentCommit = commits[i];
        const currentIndex = indexOf(selectedCommits, currentCommit);

        if (isChecked && currentIndex === -1) {
          selectedCommits.push(currentCommit);
        } else if (!isChecked && currentIndex !== -1) {
          selectedCommits.splice(currentIndex, 1);
        }

        lastSelectedCommit = currentCommit;
      }
    });

  appStore.setSelectedCommits(selectedCommits);
  appStore.setLastSelectedCommit(isBulk ? null : lastSelectedCommit);
  commitsTableStore.setSelectedCommits(selectedCommits);
}

export function clearSelection() {
  appStore.setSelectedCommits([]);
  appStore.setLastSelectedCommit(null);
  commitsTableStore.setSelectedCommits([]);
}
