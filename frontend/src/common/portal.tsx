/// <reference path='../../typings/typings.d.ts' />

import * as React from 'react';
import * as DOM from 'react-dom';
import ConfirmDialog from './ConfirmDialog.react';
import Modal from './Modal.react';

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
    <Modal title={title}>
      <ConfirmDialog message={body} {...options} />
    </Modal>
  );
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
