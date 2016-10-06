import { action, computed, observable } from 'mobx';

class LoadingStore {

  @observable progress: number = 100;

  @computed get isLoading() {
    return this.progress !== 100;
  }

  @action setLoading = (isLoading: boolean) => {
    this.progress = isLoading ? 0 : 100;
  };

  @action setProgress = (progress: ProgressEvent | number) => {
    if (typeof progress === 'number') {
      this.progress = progress;
    } else if (progress.total > 0) {
      this.progress = progress.loaded / progress.total * 100;
    }
  };

}

const loadingStore = new LoadingStore();

export { LoadingStore };
export default loadingStore;
