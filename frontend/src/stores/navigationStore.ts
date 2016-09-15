import { action, computed, observable } from 'mobx';

import AppStore from './appStore';

class NavigationStore {
  @observable query: string = '';

  @computed get enableActions() {
    return !AppStore.isDirtyWorkingDirectory;
  }

  @computed get changes() {
    return AppStore.selectedCommits.length;
  }

  @computed get hashes() {
    return AppStore.selectedCommits.map((commit: Commit) => commit.hash);
  }

  @action
  changeFilterQuery = (query: string) => {
    this.query = query;
  };

  @action
  filter = () => {
    AppStore.filter();
  };

  @action
  clearSelection = () => {
    AppStore.clearSelection();
  };

  @action
  undoCommits = (commits: string[]) => {
    AppStore.undoCommits(commits);
  };
}

const navigationStore = new NavigationStore();

export { NavigationStore };
export default navigationStore;
