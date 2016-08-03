import * as React from 'react';

import * as portal from './portal';

import './Modal.less';

interface ModalProps extends React.Props<JSX.Element> {
  onClose?: Function;
  enableBackgroundClickToClose?: boolean;
  showCloseIcon?: boolean;
  title?: React.ReactNode;
  children?: React.ReactElement<any>;
}

export default class Modal extends React.Component<ModalProps, any> {

  static defaultProps = {
    enableBackgroundClickToClose: true,
    showCloseIcon: true,
  };

  componentDidMount() {
    const content = this.refs['content'] as HTMLElement;
    content.focus();
  }

  componentDidUpdate() {
    const content = this.refs['content'] as HTMLElement;
    content.focus();
  }

  onKeyDown = (e) => {
    if (e.keyCode === 27 && this.props.showCloseIcon) {
      this.onCloseModal(e);
    }
  };

  onBackgroundClick = (e) => {
    if (this.props.enableBackgroundClickToClose && e.target.getAttribute('data-clickcatcher')) {
      this.onCloseModal(e);
    }
  };

  onCloseModal = (e) => {
    e.stopPropagation();

    if (typeof this.props.onClose === 'function') {
      this.props.onClose();
    }
    portal.closePortal();
  };

  render() {
    return (
      <div className='Modal-container' onClick={this.onBackgroundClick} data-clickcatcher={true}>
        <div ref='content' className='Modal-content' tabIndex={-1} onKeyDown={this.onKeyDown}>
          <div className='Modal-header'>
            <h3 className='Modal-title'>{this.props.title}</h3>
            {this.props.showCloseIcon
              ? <a href='#' className='Modal-close' onClick={this.onCloseModal}>&times;</a>
              : null
            }
          </div>
          <div className='Modal-body'>
            {this.props.children}
          </div>
        </div>
      </div>
    );
  }
}
