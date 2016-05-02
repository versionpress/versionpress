/// <reference path='../../typings/browser.d.ts' />

import * as React from 'react';
import * as portal from './portal';

import './Modal.less';

interface ModalProps extends React.Props<JSX.Element> {
  closeModalCallback?: Function;
  backgroundClickToClose?: boolean;
  showCloseIcon?: boolean;
  title?: React.ReactNode;
  children?: React.ReactElement<any>;
}

export default class Modal extends React.Component<ModalProps, any> {

  constructor(props) {
    super(props);

    this.closeModalHandler = this.closeModalHandler.bind(this);
    this.backgroundClickHandler = this.backgroundClickHandler.bind(this);
    this.keyDownHandler = this.keyDownHandler.bind(this);
  }

  static defaultProps = {
    backgroundClickToClose: true,
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

  getCloseIconMarkup() {
    return this.props.showCloseIcon
      ? <a href='#' className='Modal-close' onClick={this.closeModalHandler}>&times;</a>
      : null;
  }

  render() {
    return (
      <div className='Modal-container' onClick={this.backgroundClickHandler} data-clickcatcher={true}>
        <div ref='content' className='Modal-content' tabIndex={-1} onKeyDown={this.keyDownHandler}>
          <div className='Modal-header'>
            <h3 className='Modal-title'>{this.props.title}</h3>
            {this.getCloseIconMarkup()}
          </div>
          <div className='Modal-body'>
            {this.props.children}
          </div>
        </div>
      </div>
    );
  }

  keyDownHandler(e) {
    if (e.keyCode === 27 && this.props.showCloseIcon) {
      this.closeModalHandler(e);
    }
  }

  backgroundClickHandler(e) {
    if (this.props.backgroundClickToClose && e.target.getAttribute('data-clickcatcher')) {
      this.closeModalHandler(e);
    }
  }

  closeModalHandler(e) {
    e.stopPropagation();

    if (typeof this.props.closeModalCallback === 'function') {
      this.props.closeModalCallback();
    }
    portal.closePortal();
  }

}
