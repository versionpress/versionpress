/// <reference path='../interfaces/Visualisation.d.ts' />
/// <reference path='../components/common/Commits.d.ts' />

import { action, computed, observable } from 'mobx';
import * as _ from 'lodash';

import appStore from './appStore';
import { indexOf } from '../utils/CommitUtils';
import { generateGraphData } from '../actions/utils';
import CommitRow from '../entities/CommitRow';

class CommitsTableStore {

  @observable commitRows: CommitRow[] = [];
  @observable pages: number[] = [];
  @observable showVisualisation: boolean = localStorage
    ? !!localStorage.getItem('showVisualization')
    : false;

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
      const { branch } = commitRow.visualisation;
      if (branches.indexOf(branch) === -1) {
        branches.push(branch);
      }
    });

    return branches.length;
  }

  @action changeShowVisualisation = () => {
    this.showVisualisation = !this.showVisualisation;

    if (localStorage) {
      localStorage.setItem('showVisualization', this.showVisualisation ? 'true' : '');
    }
  };

  @action setCommitRows = (commitRows: CommitRow[]) => {
    this.commitRows = commitRows;

    let graphStructure = [];
    this.commits.forEach(commit => {
      graphStructure.push({
        sha: commit.hash,
        parents: commit.parentHashes,
        environment: commit.environment,
      });
    });

    const visualization = generateGraphData(graphStructure);

    let environments = {};
    this.commitRows.forEach((commitRow, i) => {
      let upper, lower;

      lower = i === this.commitRows.length - 1
        ? null
        : visualization[i];

      upper = i === 0
        ? null
        : visualization[i - 1];

      commitRow.setVisualisation({
        upperRoutes: upper ? upper.routes : null,
        lowerRoutes: lower ? lower.routes : null,
        environment: visualization[i].environment,
        branch: visualization[i].branch,
        offset: visualization[i].offset,
        isLastEnvCommit: !environments[visualization[i].environment],
      });

      if (!environments[visualization[i].environment]) {
        environments[visualization[i].environment] = true;
      }
    });
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
