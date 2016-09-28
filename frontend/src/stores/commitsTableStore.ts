/// <reference path='../interfaces/Visualization.d.ts' />

import { action, computed, observable } from 'mobx';
import * as _ from 'lodash';
import CommitRow from './CommitRow';

import appStore from './appStore';
import { indexOf } from "../utils/CommitUtils";
import { generateGraphData } from "./utils";

class CommitsTableStore {
  @observable commitRows: CommitRow[] = [];
  @observable pages: number[] = [];
  @observable showVisualization: boolean = true;

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

  @computed get branches() {
    let branches = [];

    this.commitRows.forEach(commitRow => {
      const { branch } = commitRow.visualization;
      if (branches.indexOf(branch) === -1) {
        branches.push(branch);
      }
    });

    return branches.length;
  }

/*
  @computed get visualizationData() {
    let graphStructure = [];
    for (let i = 0; i < this.commits.length; i++) {
      const commit = this.commits[i];
      let parents = [];

      if (commit.hash === "8f3c2d0a7161f1aa51b60eee06ce9c32644a9417") {
        parents = ["cf9d842a5fe9a6f5878d4505ed301f28bbd2db77"];
      } else if (commit.hash === "202c87ebc6468ee2c272c488687bcb9315f88729") {
        parents = ["b8adb93da34462f3016d81636d86cd97d6dbf791", "5e6ca8ab6303bf02b3cd26a74a499fea4584e945"];
      } else if (i === this.commits.length - 1) {
        parents = [];
      } else {
        parents = [this.commits[i + 1].hash];
      }

      graphStructure.push({
        sha: commit.hash,
        parents: parents,
        environment: commit.environment
      });
    }

    const visualization = generateGraphData([
      {
        sha: '9',
        parents: ['10'],
        environment: 'master'
      },
      {
        sha: '10',
        parents: ['11', '30'],
        environment: 'master'
      },
      {
        sha: '11',
        parents: ['12'],
        environment: 'master'
      },
      {
        sha: '12',
        parents: ['13', '20'],
        environment: 'master'
      },
      {
        sha: '20',
        parents: ['21'],
        environment: 'staging'
      },
      {
        sha: '30',
        parents: ['31'],
        environment: 'beta-test'
      },
      {
        sha: '21',
        parents: ['22'],
        environment: 'staging'
      },
      {
        sha: '31',
        parents: ['13'],
        environment: 'beta-test'
      },
      {
        sha: '13',
        parents: ['14'],
        environment: 'master'
      },
      {
        sha: '22',
        parents: ['14'],
        environment: 'staging'
      },
      {
        sha: '14',
        parents: ['15'],
        environment: 'master'
      },
      {
        sha: '15',
        parents: [],
        environment: 'master'
      }
    ]);


    return generateGraphData(graphStructure);
  }
 */
  @action
  changeCommitRows = (commitRows: CommitRow[]) => {
    this.commitRows = commitRows;

    let graphStructure = [];
    for (let i = 0; i < this.commits.length; i++) {
      const commit = this.commits[i];
      let parents = [];

      if (commit.hash === "8f3c2d0a7161f1aa51b60eee06ce9c32644a9417") {
        parents = ["cf9d842a5fe9a6f5878d4505ed301f28bbd2db77"];
      } else if (commit.hash === "202c87ebc6468ee2c272c488687bcb9315f88729") {
        parents = ["b8adb93da34462f3016d81636d86cd97d6dbf791", "5e6ca8ab6303bf02b3cd26a74a499fea4584e945"];
      } else if (i === this.commits.length - 1) {
        parents = [];
      } else {
        parents = [this.commits[i + 1].hash];
      }

      graphStructure.push({
        sha: commit.hash,
        parents: parents,
        environment: commit.environment
      });
    }

    const visualization = generateGraphData(graphStructure);

    this.commitRows.forEach((commitRow, i) => {
      let upper, lower;

      lower = i === this.commitRows.length - 1
        ? null
        : visualization[i];

      upper = i === 0
        ? null
        : visualization[i - 1];

      commitRow.visualization = {
        upperRoutes: upper ? upper.routes : null,
        lowerRoutes: lower ? lower.routes : null,
        environment: visualization[i].environment,
        branch: visualization[i].branch,
        offset: visualization[i].offset
      };
    });
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
