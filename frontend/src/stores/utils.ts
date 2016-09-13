import * as request from 'superagent';

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