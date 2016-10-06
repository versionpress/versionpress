import { runInAction } from 'mobx';
import * as request from 'superagent';

import * as WpApi from '../services/WpApi';
import { getErrorMessage } from './utils';
import { fetchCommits } from '../actions';
import { appStore, servicePanelStore, loadingStore } from '../stores';

export function commit(message: string) {
  loadingStore.setLoading(true);

  WpApi
    .post('commit')
    .send({ 'commit-message': message })
    .on('progress', loadingStore.setProgress)
    .end(commitDiscardEnd('Changes have been committed.'));
}

export function discard() {
  loadingStore.setLoading(false);

  WpApi
    .post('discard-changes')
    .on('progress', loadingStore.setProgress)
    .end(commitDiscardEnd('Changes have been discarded.'));
}

function commitDiscardEnd(successMessage: string) {
  return (err: any, res: request.Response) => {
    runInAction(() => {
      loadingStore.setLoading(false);

      if (err) {
        servicePanelStore.setMessage(getErrorMessage(res, err));
      } else {
        appStore.setDirtyWorkingDirectory(false);
        servicePanelStore.setMessage({
          code: 'updated',
          message: successMessage,
        });
        fetchCommits();
      }
    });

    return !err;
  };
}
