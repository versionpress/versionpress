/// <reference path='../../typings/typings.d.ts' />

import * as request from 'superagent';
import config from '../config';

const noCache = function (request) {
  var timestamp = Date.now().toString();
  request.query(timestamp);
  return request;
};

export function getApiLink(endpoint: string) {
  if (/^\/index.php\/.*/.test(<string> config.api.permalinkStructure)) {
    return config.api.root + '/index.php/' + config.api.urlPrefix + '/versionpress/' + endpoint;
  } else if (config.api.permalinkStructure) {
    return config.api.root + '/' + config.api.urlPrefix + '/versionpress/' + endpoint;
  } else {
    return config.api.root + '/?' + config.api.queryParam + '=/versionpress/' + endpoint;
  }
}

export function get(endpoint: string) {
  const req = request
    .get(getApiLink(endpoint))
    .accept('application/json')
    .use(noCache);

  return config.api.nonce
    ? req.set('X-WP-Nonce', config.api.nonce)
    : req;
}

export function post(endpoint: string) {
  const req = request
    .post(getApiLink(endpoint))
    .accept('application/json');

  return config.api.nonce
    ? req.set('X-WP-Nonce', config.api.nonce)
    : req;
}
