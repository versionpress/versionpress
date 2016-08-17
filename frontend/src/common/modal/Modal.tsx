import * as React from 'react';

import Body from './Body';
import Header from './header/Header';
import * as portal from '../portal';

import './Modal.less';

interface ModalProps {
  enableBackgroundClickToClose?: boolean;
  showCloseIcon?: boolean;
  title?: React.ReactNode;
  children?: React.ReactElement<any>;
  onClose?(): void;
}

export default class Modal extends React.Component<ModalProps, {}> {

  static defaultProps = {
    enableBackgroundClickToClose: true,
    showCloseIcon: true,
    onClose: () => {},
  };

  componentDidMount() {
    const content = this.refs['content'] as HTMLElement;
    content.focus();
  }

  componentDidUpdate() {
    const content = this.refs['content'] as HTMLElement;
    content.focus();
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
      <div
        className='Modal-container'
        onClick={this.onBackgroundClick}
      >
        <div
          ref='content'
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
