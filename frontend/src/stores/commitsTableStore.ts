import { action, computed, observable } from 'mobx';
import * as _ from 'lodash';
import CommitRow from './CommitRow';

import appStore from './appStore';
import { indexOf } from "../utils/CommitUtils";
import { generateGraphData } from "./utils";

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

  @computed get visualizationData() {
    console.log(this.commits.length);
    const visualization = generateGraphData([
      {
        sha: '9',
        parents: ['10']
      },
      {
        sha: '10',
        parents: ['11', '30']
      },
      {
        sha: '11',
        parents: ['12']
      },
      {
        sha: '12',
        parents: ['13', '20']
      },
      {
        sha: '20',
        parents: ['21']
      },
      {
        sha: '30',
        parents: ['31']
      },
      {
        sha: '21',
        parents: ['22']
      },
      {
        sha: '31',
        parents: ['13']
      },
      {
        sha: '13',
        parents: ['14']
      },
      {
        sha: '22',
        parents: ['14']
      },
      {
        sha: '14',
        parents: ['15']
      },
      {
        sha: '15',
        parents: []
      }
    ]);

    console.log(visualization);

    return this.commits.length;
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
