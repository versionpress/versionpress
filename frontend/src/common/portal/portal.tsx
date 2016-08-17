import * as React from 'react';
import * as DOM from 'react-dom';

import ConfirmDialog from '../confirm-dialog/ConfirmDialog';
import Modal from '../modal/Modal';

let portalNode;

export function alertDialog(title: React.ReactNode, body: React.ReactNode) {
  closePortal();
  openPortal(
    <Modal title={title}>
      {body}
    </Modal>
  );
}

export function confirmDialog(
  title: React.ReactNode,
  body: React.ReactNode,
  onOkClick: () => void | boolean,
  onCancelClick: () => void | boolean,
  options: any = {}
  ) {
  if (onOkClick) {
    options.onOkButtonClick = onOkClick;
  }

  if (onCancelClick) {
    options.onCancelButtonClick = onCancelClick;
  }

  closePortal();
  openPortal(
    <Modal
      title={title}
      onClose={onCancelClick}
    >
      <ConfirmDialog
        message={body}
        {...options}
      />
    </Modal>
  );
}

export function openPortal(children: React.ReactElement<any>) {
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
