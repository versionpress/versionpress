import * as React from 'react';
import { observer } from 'mobx-react';

import Body from './body/Body';
import Header from './header/Header';
import * as portal from '../portal/portal';

import './Modal.less';

interface ModalProps {
  enableBackgroundClickToClose?: boolean;
  showCloseIcon?: boolean;
  title?: React.ReactNode;
  children?: React.ReactNode;
  onClose?(): void;
}

@observer
export default class Modal extends React.Component<ModalProps, {}> {

  static defaultProps = {
    enableBackgroundClickToClose: true,
    showCloseIcon: true,
    onClose: () => {},
  };

  contentNode: HTMLDivElement = null;

  componentDidMount() {
    this.contentNode.focus();
  }

  componentDidUpdate() {
    this.contentNode.focus();
  }

  onBackgroundClick = (e: React.MouseEvent) => {
    e.stopPropagation();

    if (this.props.enableBackgroundClickToClose) {
      this.closeModal();
    }
  };

  onContentKeyDown = (e: React.KeyboardEvent) => {
    if (e.keyCode === 27 && this.props.showCloseIcon) {
      this.closeModal();
    }
  };

  onCloseHeaderClick = (e: React.MouseEvent) => {
    e.stopPropagation();

    this.closeModal();
  };

  private closeModal = () => {
    this.props.onClose();
    portal.closePortal();
  };

  render() {
    const { children, showCloseIcon, title } = this.props;

    return (
      <div className='Modal-container' onClick={this.onBackgroundClick}>
        <div
          ref={node => this.contentNode = node}
          className='Modal-content'
          tabIndex={-1}
          onKeyDown={this.onContentKeyDown}
          onClick={e => e.stopPropagation()}
        >
          <Header
            title={title}
            showCloseIcon={showCloseIcon}
            onCloseClick={this.onCloseHeaderClick}
          />
          <Body>
          {children}
          </Body>
        </div>
      </div>
    );
  }

}
