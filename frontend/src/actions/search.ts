import * as request from 'superagent';

import * as WpApi from '../services/WpApi';

import { searchStore } from '../stores';

export function fetchSearchConfig() {
  WpApi
    .get('autocomplete-config')
    .end((err: any, res: request.Response) => {
      const data = res.body.data as SearchConfig;
      searchStore.setConfig(data);
    });
}
