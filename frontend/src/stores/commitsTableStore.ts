/// <reference path='../components/common/Commits.d.ts' />

import { action, computed, observable } from 'mobx';
import * as _ from 'lodash';

import appStore from './appStore';
import CommitRow from '../entities/CommitRow';
import { indexOf } from '../utils/CommitUtils';

class CommitsTableStore {

  @observable commitRows: CommitRow[] = [];
  @observable pages: number[] = [];

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

  @action setCommitRows = (commitRows: CommitRow[]) => {
    this.commitRows = commitRows;
  };

  @action setPages = (pages: number[]) => {
    this.pages = pages;
  };

  @action setSelectedCommits = (selectedCommits: Commit[]) => {
    this.commitRows.forEach(commitRow => {
      commitRow.isSelected = indexOf(selectedCommits, commitRow.commit) !== -1;
    });
  };

  @action reset = () => {
    this.commitRows = [];
    this.pages = [];
  }

}

const commitsTableStore = new CommitsTableStore();

export { CommitsTableStore };
export default commitsTableStore;
