import { action, computed, observable } from 'mobx';

import appStore from './appStore';

class NavigationStore {
  @observable query: string = '';

  @computed get enableActions() {
    return !appStore.isDirtyWorkingDirectory;
  }

  @computed get changes() {
    return appStore.selectedCommits.length;
  }

  @computed get hashes() {
    return appStore.selectedCommits.map((commit: Commit) => commit.hash);
  }

  @action
  changeFilterQuery = (query: string) => {
    this.query = query;
  };

  @action
  filter = () => {
    appStore.filter();
  };

  @action
  clearSelection = () => {
    appStore.clearSelection();
  };

  @action
  undoCommits = (commits: string[]) => {
    appStore.undoCommits(commits);
  };
}

const navigationStore = new NavigationStore();

export { NavigationStore };
export default navigationStore;
