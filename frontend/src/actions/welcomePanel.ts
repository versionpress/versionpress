import * as request from 'superagent';

import * as WpApi from '../services/WpApi';
import { fetchCommits } from '../actions';
import { appStore } from '../stores';

export function fetchWelcomePanel() {
  WpApi
    .get('display-welcome-panel')
    .end((err: any, res: request.Response) => {
      const data = res.body.data as VpApi.DisplayWelcomePanelResponse;

      if (err) {
        return;
      }

      appStore.setDisplayWelcomePanel(data === true);
    });
}

export function hideWelcomePanel () {
  appStore.setDisplayWelcomePanel(false);

  WpApi
    .post('hide-welcome-panel')
    .end((err: any, res: request.Response) => {
      fetchCommits();
    });
}
