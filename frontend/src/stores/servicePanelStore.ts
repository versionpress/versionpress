import { action, computed, observable } from 'mobx';
import * as moment from 'moment';

import BranchCommit from './BranchCommit';
import commitsTableStore from './commitsTableStore';

function getRandomCommit(id: number, isMerge: boolean, environment: string): Commit {
  const rnd = Math.random();
  return {
    message: isMerge ? 'Merged some changes' : rnd > .7 ? `Reverted ${id}` : `Made some changes ${id}`,
    date: moment().add(-id, 'day').toISOString(),
    hash: `abcd${id}`,
    canUndo: true,
    canRollback: true,
    isEnabled: true,
    isInitial: false,
    isMerge: isMerge,
    environment: environment,
    changes: [],
    author: {
      name: 'Some Name',
      email: 'some@email.gg',
      avatar: ''
    }
  }
}

class ServicePanelStore {
  @observable message: InfoMessage = null;
  @observable isVisible: boolean = false;
  @observable isVisualizationVisible: boolean = false;

  get commits() {
    return commitsTableStore.commits;
  }

  @computed get environments() {
    let environments = [];
    this.commits.forEach(commit => {
      if (environments.indexOf(commit.environment) === -1) {
        environments.push(commit.environment);
      }
    });

    return environments;
  }

  @computed get visualization() {
    this.commits.forEach(commit => commit.environment);

    return [
      [new BranchCommit(getRandomCommit(1, false, 'master')), null],
      [new BranchCommit(getRandomCommit(2, true, 'master')), null],
      [new BranchCommit(null, true, 'master'),
        new BranchCommit(getRandomCommit(3, false, 'staging'), false, null, false, false, true)],
      [new BranchCommit(getRandomCommit(4, false, 'master')),
        new BranchCommit(null, true, 'staging')],
      [new BranchCommit(null, true, 'master'),
        new BranchCommit(getRandomCommit(5, false, 'staging'))],
      [new BranchCommit(null, true, 'master'),
        new BranchCommit(getRandomCommit(6, false, 'staging'), false, null, true)],
      [new BranchCommit(getRandomCommit(7, false, 'master'), false, null, false, true), null],
      [new BranchCommit(getRandomCommit(8, false, 'master')), null]
    ];
  }

  @action
  changeMessage = (message: InfoMessage) => {
    this.message = message;
  };

  @action
  changeVisibility = (isVisible?: boolean) => {
    this.isVisible = typeof isVisible === 'boolean' ? isVisible : !this.isVisible;
    this.changeVisualizationVisibility();
  };

  @action
  changeVisualizationVisibility = (isVisible?: boolean) => {
    this.isVisualizationVisible = typeof isVisible === 'boolean'
      ? isVisible
      : !this.isVisualizationVisible;
  };
}

const servicePanelStore = new ServicePanelStore();

export { ServicePanelStore };
export default servicePanelStore;
