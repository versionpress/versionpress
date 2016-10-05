import { runInAction } from 'mobx';
import * as request from 'superagent';

import * as WpApi from '../services/WpApi';
import { getErrorMessage } from './utils';
import { fetchCommits } from '../actions';
import { appStore, servicePanelStore, uiStore } from '../stores';

export function commit(message: string) {
  uiStore.setLoading(true);

  WpApi
    .post('commit')
    .send({ 'commit-message': message })
    .on('progress', uiStore.setProgress)
    .end(commitDiscardEnd('Changes have been committed.'));
}

export function discard() {
  uiStore.setLoading(false);

  WpApi
    .post('discard-changes')
    .on('progress', uiStore.setProgress)
    .end(commitDiscardEnd('Changes have been discarded.'));
}

function commitDiscardEnd(successMessage: string) {
  return (err: any, res: request.Response) => {
    runInAction(() => {
      uiStore.setLoading(false);

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
