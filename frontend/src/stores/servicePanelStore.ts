import { action, computed, observable } from 'mobx';
import * as moment from 'moment';

import BranchCommit from './BranchCommit';
import commitsTableStore from './commitsTableStore';

function getRndCom(id: number, isMerge: boolean, environment: string): Commit {
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

  get commits() {
    return commitsTableStore.commits;
  }

  @computed get environments(): string[] {
    /*
    let environments = [];
    this.commits.forEach(commit => {
      if (environments.indexOf(commit.environment) === -1) {
        environments.push(commit.environment);
      }
    });

    return environments;
     */
    return ['master', 'staging', 'beta'];
  }

  @computed get visualization(): BranchCommit[][] {
    return [
      [
        new BranchCommit(getRndCom(1, false, 'master')),
        null,
        null
      ],
      [
        new BranchCommit(getRndCom(9, true, 'master'), { mergeParents: ['beta'] }),
        null,
        null
      ],
      [
        new BranchCommit(null, { environment: 'master' }),
        null,
        new BranchCommit(getRndCom(30, false, 'beta'), { mergeTo: ['master'], isEnd: true })
      ],
      [
        new BranchCommit(getRndCom(2, true, 'master'), { mergeParents: ['staging'] }),
        null,
        new BranchCommit(null, { environment: 'beta' })
      ],
      [
        new BranchCommit(null, { environment: 'master' }),
        new BranchCommit(getRndCom(3, false, 'staging'), { mergeTo: ['master'], isEnd: true }),
        new BranchCommit(null, { environment: 'beta' })
      ],
      [
        new BranchCommit(null, { environment: 'master' }),
        new BranchCommit(null, { environment: 'staging' }),
        new BranchCommit(getRndCom(20, false, 'beta'), { checkoutFrom: 'master' })
      ],
      [
        new BranchCommit(getRndCom(4, false, 'master'), { checkoutChildren: ['beta'] }),
        new BranchCommit(null, { environment: 'staging' }),
        null
      ],
      [
        new BranchCommit(null, { environment: 'master' }),
        new BranchCommit(getRndCom(5, false, 'staging')),
        null
      ],
      [
        new BranchCommit(null, { environment: 'master' }),
        new BranchCommit(getRndCom(6, false, 'staging'), { checkoutFrom: 'master' }),
        null
      ],
      [
        new BranchCommit(getRndCom(7, false, 'master'), { checkoutChildren: ['staging'] }),
        null,
        null
      ],
      [
        new BranchCommit(getRndCom(8, false, 'master')),
        null,
        null
      ]
    ];
  }

  @action
  changeMessage = (message: InfoMessage) => {
    this.message = message;
  };

  @action
  changeVisibility = (isVisible?: boolean) => {
    this.isVisible = typeof isVisible === 'boolean' ? isVisible : !this.isVisible;
  };
}

const servicePanelStore = new ServicePanelStore();

export { ServicePanelStore };
export default servicePanelStore;