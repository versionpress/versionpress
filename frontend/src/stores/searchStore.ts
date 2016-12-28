/// <reference path='../components/search/Search.d.ts' />

import { action, observable } from 'mobx';

class SearchStore {

  @observable config: SearchConfig;

  @action setConfig = (config: SearchConfig) => {
    this.config = config;
  };

}

const searchStore = new SearchStore();

export { SearchStore };
export default searchStore;
