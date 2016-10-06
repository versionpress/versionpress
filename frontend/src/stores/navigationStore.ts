/// <reference path='../components/common/Commits.d.ts' />

import { action, computed, observable } from 'mobx';

import appStore from './appStore';

class NavigationStore {

  @observable query: string = '';

  @computed get changesCount() {
    return appStore.selectedCommits.length;
  }

  @computed get hashes() {
    return appStore.selectedCommits.map((commit: Commit) => commit.hash);
  }

  @action changeFilterQuery = (query: string) => {
    this.query = query;
  };

}

const navigationStore = new NavigationStore();

export { NavigationStore };
export default navigationStore;
