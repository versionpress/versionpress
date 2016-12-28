import { runInAction } from 'mobx';
import * as request from 'superagent';

import * as WpApi from '../services/WpApi';
import { appStore, commitsTableStore, navigationStore, loadingStore } from '../stores';

export function checkUpdate() {
  const { isLoading } = loadingStore;
  const { commits } = commitsTableStore;

  if (!commits.length || isLoading) {
    return;
  }

  WpApi
    .get('should-update')
    .query({
      query: encodeURIComponent(navigationStore.activeQuery),
      latestCommit: commits[0].hash,
    })
    .end((err: any, res: request.Response) => {
      const data = res.body.data as VpApi.ShouldUpdateResponse;

      runInAction(() => {
        if (err) {
          appStore.setDisplayUpdateNotice(false);
          appStore.setDirtyWorkingDirectory(false);
          clearInterval(appStore.refreshInterval);
        } else {
          appStore.setDisplayUpdateNotice(!appStore.page && data.update === true);
          appStore.setDirtyWorkingDirectory(data.cleanWorkingDirectory !== true);
        }
      });
    });
}
