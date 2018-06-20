import * as React from 'react';
import * as DOM from 'react-dom';
import * as request from 'superagent';

import ConfirmDialog, { ConfirmDialogProps } from '../dialogs/ConfirmDialog';
import UndoEnabledDialog from '../dialogs/UndoEnabledDialog';
import UndoDisabledDialog from '../dialogs/UndoDisabledDialog';
import Modal from '../modal/Modal';
import * as WpApi from '../../services/WpApi';

let portalNode: Element | null = null;

export function alertDialog(title: React.ReactNode, body: React.ReactNode) {
  closePortal();
  openPortal(
    <Modal title={title}>
      {body}
    </Modal>
  );
}

export function confirmDialog(title: React.ReactNode, body: React.ReactNode, options: ConfirmDialogProps = {}) {
  const cancelHandler = options.onCancelButtonClick;
  closePortal();
  openPortal(
    <Modal title={title} onClose={cancelHandler}>
      <ConfirmDialog message={body} {...options} />
    </Modal>
  );
}

export function revertDialog(title: React.ReactNode, okHandler: () => void) {
  const req = WpApi
    .get('can-revert')
    .end((err: any, res: request.Response) => {
      const data = res.body.data as VpApi.CanRevertResponse;
      let body, options: ConfirmDialogProps;

      if (data === true) {
        body = <UndoEnabledDialog />;
        options = { onOkButtonClick: okHandler };
      } else {
        body = <UndoDisabledDialog />;
        options = { okButtonClasses: 'disabled' };
      }

      confirmDialog(title, body, options);
    });

  const options = {
    isLoading: true,
    cancelHandler: () => { req.abort(); },
  };
  confirmDialog(title, '', options);
}

export function openPortal(children: any) {
  portalNode = document.createElement('div');
  document.body.appendChild(portalNode);
  DOM.render(children, portalNode);
}

export function closePortal() {
  if (portalNode && portalNode.parentNode && close) {
    DOM.unmountComponentAtNode(portalNode.parentNode as Element);
    portalNode.parentNode.removeChild(portalNode);
    portalNode = null;
  }
}
