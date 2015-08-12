/// <reference path='../../typings/tsd.d.ts' />

import React = require('react');
import portal = require('../common/portal');
import request = require('superagent');
import WpApi = require('../services/WpApi');

const DOM = React.DOM;

export function revertDialog(title: React.ReactNode, okHandler: Function) {
  portal.confirmDialog(title, '', () => {}, () => {}, {loading: true});

  request
    .get(WpApi.getApiLink('can-revert'))
    .set('Accept', 'application/json')
    .end((err: any, res: request.Response) => {
      if (res.body) {
        const body = DOM.div(null,
          DOM.p(null,
            'For EAP releases, please have a backup. ',
            DOM.a({
              href: 'http://docs.versionpress.net/en/feature-focus/undo-and-rollback',
              target: '_blank'
            }, 'Learn more about reverts.')
          )
        );
        portal.confirmDialog(title, body, okHandler, () => {}, {});
    } else {
        const body = DOM.div(null,
          DOM.p({className: 'undo-warning'},
            DOM.span({className: 'icon icon-warning'}),
            'You have ',
            DOM.a({
              href: 'http://docs.versionpress.net/en/feature-focus/undo-and-rollback#uncommitted-files',
              target: '_blank'
            }, 'uncommitted changes'),
            ' in your WordPress directory.', DOM.br(),
            'Please commit them before doing a revert.'
          )
        );
        portal.confirmDialog(title, body, () => {}, () => {}, {okButtonClasses: 'disabled'});
      }
    });
}
