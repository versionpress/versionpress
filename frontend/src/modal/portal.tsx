import * as React from 'react';
import * as DOM from 'react-dom';
import * as request from 'superagent';

import UndoEnabledDialog from './UndoEnabledDialog';
import UndoDisabledDialog from './UndoDisabledDialog';
import ConfirmDialog from './dialogs/ConfirmDialog.react';
import Modal from './Modal.react';
import * as WpApi from '../../services/WpApi';

var portalNode;

export function alertDialog(title: React.ReactNode, body: React.ReactNode) {
  closePortal();
  openPortal(
    <Modal title={title}>
      {body}
    </Modal>
  );
}

export function confirmDialog(title: React.ReactNode, body: React.ReactNode, okHandler, cancelHandler, options) {
  options = options || {};
  if (okHandler) {
    options.okButtonClickHandler = okHandler;
  }
  if (cancelHandler) {
    options.cancelButtonClickHandler = cancelHandler;
  }
  closePortal();
  openPortal(
    <Modal title={title} onClose={cancelHandler}>
      <ConfirmDialog message={body} {...options} />
    </Modal>
  );
}

export function revertDialog(title: React.ReactNode, okHandler: Function) {
  const req = WpApi
    .get('can-revert')
    .end((err: any, res: request.Response) => {
      const data = res.body.data as VpApi.CanRevertResponse;
      if (data === true) {
        const body = <UndoEnabledDialog />;
        portal.confirmDialog(title, body, okHandler, () => {}, {});
      } else {
        const body = <UndoDisabledDialog />;
        portal.confirmDialog(title, body, () => {}, () => {}, {okButtonClasses: 'disabled'});
      }
    });

  const cancelHandler = () => { req.abort(); };
  portal.confirmDialog(title, '', () => {}, cancelHandler, {isLoading: true});
}

export function openPortal(children) {
  portalNode = document.createElement('div');
  document.body.appendChild(portalNode);
  DOM.render(children, portalNode);
}

export function closePortal() {
  if (portalNode && portalNode.parentNode && close) {
    DOM.unmountComponentAtNode(portalNode.parentNode);
    portalNode.parentNode.removeChild(portalNode);
    portalNode = null;
  }
}
