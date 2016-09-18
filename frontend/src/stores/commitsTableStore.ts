import { action, computed, observable } from 'mobx';
import * as _ from 'lodash';
import CommitRow from './CommitRow';

import appStore from './appStore';
import { indexOf } from "../utils/CommitUtils";

class CommitsTableStore {
  @observable commitRows: CommitRow[] = [];
  @observable pages: number[] = [];

  @computed get enableActions() {
    return !appStore.isDirtyWorkingDirectory;
  }

  @computed get commits() {
    return this.commitRows.map(row => row.commit);
  }

  @computed get selectableCommits() {
    return this.commits.filter((commit: Commit) => commit.canUndo);
  }

  @computed get areAllCommitsSelected() {
    return this.commits.length > 0 &&
      !_.differenceBy(this.selectableCommits, appStore.selectedCommits, ((value: Commit) => value.hash)).length;
  }

  @action
  changeCommitRows = (commitRows: CommitRow[]) => {
    this.commitRows = commitRows;
  };

  @action
  changePages = (pages: number[]) => {
    this.pages = pages;
  };

  @action
  updateSelectedCommits = (selectedCommits: Commit[]) => {
    this.commitRows.forEach(commitRow => {
      commitRow.isSelected = indexOf(selectedCommits, commitRow.commit) !== -1;
    });
  };

  @action
  deselectAllCommits = () => {
    this.commitRows.forEach(commitRow => {
      commitRow.isSelected = false;
    });
  };

  @action
  undoCommits = (commits: string[]) => {
    appStore.undoCommits(commits);
  };

  @action
  rollbackToCommit = (hash: string) => {
    appStore.rollbackToCommit(hash)
  };

  @action
  selectCommits = (commitsToSelect: Commit[], isChecked: boolean, isShiftKey: boolean) => {
    appStore.selectCommits(commitsToSelect, isChecked, isShiftKey);
  };
}

const commitsTableStore = new CommitsTableStore();

export { CommitsTableStore };
export default commitsTableStore;
