import { runInAction } from 'mobx';
import * as request from 'superagent';
import { appHistory } from '../routes';

import config from '../config/config';
import * as WpApi from '../services/WpApi';
import { indexOf } from '../utils/CommitUtils';
import { getErrorMessage, parsePageNumber } from './utils';
import { checkUpdate } from '../actions';
import CommitRow from '../entities/CommitRow';
import {
  appStore,
  commitsTableStore,
  navigationStore,
  servicePanelStore,
  loadingStore,
} from '../stores';

const routes = config.routes;

export function fetchCommits (page: number | string = appStore.page) {
  loadingStore.setLoading(true);

  if (typeof page === 'string' && parsePageNumber(page) < 1) {
    page = 0;
    appHistory.replace(routes.home);
  }

  appStore.setPage(page);

  WpApi
    .get('commits')
    .query({
      page: appStore.page,
      query: encodeURIComponent(navigationStore.query),
    })
    .on('progress', loadingStore.setProgress)
    .end((err: any, res: request.Response) => {
      const data = res.body.data as VpApi.GetCommitsResponse;

      runInAction(() => {
        loadingStore.setLoading(false);
        appStore.setDisplayUpdateNotice(false);

        if (err) {
          commitsTableStore.reset();
          servicePanelStore.setMessage(getErrorMessage(res, err));
        } else {
          commitsTableStore.setPages(data.pages.map(c => c + 1));
          commitsTableStore.setCommitRows(data.commits.map(commit => (
            new CommitRow(commit, indexOf(appStore.selectedCommits, commit) !== -1))
          ));
          servicePanelStore.setMessage(null);

          checkUpdate();
        }
      });
    });
};

export function undoCommits(commits: string[]) {
  wpUndoRollback('undo', { commits: commits });
};

export function rollbackToCommit(hash: string) {
  wpUndoRollback('rollback', { commit: hash });
};

function wpUndoRollback(name: string, query: any) {
  loadingStore.setLoading(true);

  WpApi
    .get(name)
    .query(query)
    .on('progress', loadingStore.setProgress)
    .end((err: any, res: request.Response) => {
      if (err) {
        runInAction(() => {
          loadingStore.setLoading(false);
          servicePanelStore.setMessage(getErrorMessage(res, err));
        });
      } else {
        appHistory.push(routes.home);
        document.location.reload();
      }
    });
}
