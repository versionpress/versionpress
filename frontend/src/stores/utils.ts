import * as request from 'superagent';
import * as Promise from 'core-js/es6/promise';
import * as WpApi from '../services/WpApi';

export function getErrorMessage(res: request.Response, err: any) {
  if (res) {
    const body = res.body;
    if ('code' in body && 'message' in body) {
      return body;
    }
  }
  console.error(err);
  return {
    code: 'error',
    message: 'VersionPress is not able to connect to WordPress site. Please try refreshing the page.',
    details: err,
  };
}

export function parsePageNumber(page: string) {
  return (parseInt(page, 10) - 1) || 0;
}

export function getGitStatus() {
  return new Promise((resolve, reject) => {
    WpApi
      .get('git-status')
      .end((err, res: request.Response) => {
        const data = res.body.data as VpApi.GetGitStatusResponse;
        if (err) {
          reject(getErrorMessage(res, err));
        } else {
          resolve(data);
        }
      });
  });
}

export function getDiff(hash: string) {
  return new Promise((resolve, reject) => {
    WpApi
      .get('diff')
      .query(hash === '' ? null : { commit: hash })
      .end((err, res: request.Response) => {
        const data = res.body.data as VpApi.GetDiffResponse;
        if (err) {
          reject(getErrorMessage(res, err));
        } else {
          resolve(data.diff);
        }
      });
  });
}
