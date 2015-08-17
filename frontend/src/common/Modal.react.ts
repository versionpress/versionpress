/// <reference path='../../typings/tsd.d.ts' />

import React = require('react');
import portal = require('./portal');

require('./Modal.less');

const DOM = React.DOM;

interface ModalProps {
  closeModalCallback?: Function;
  backgroundClickToClose?: boolean;
  showCloseIcon?: boolean;
  title?: React.ReactNode;
  children?: React.ReactElement<any>;
}

class Modal extends React.Component<ModalProps, any> {

  constructor(props) {
    super(props);

    this.closeModalHandler = this.closeModalHandler.bind(this);
    this.backgroundClickHandler = this.backgroundClickHandler.bind(this);
    this.keyDownHandler = this.keyDownHandler.bind(this);
  }

  static defaultProps = {
    backgroundClickToClose: true,
    showCloseIcon: true
  };

  componentDidMount() {
    const content = <any> this.refs['content'];
    content.getDOMNode().focus();
  }

  componentDidUpdate() {
    const content = <any> this.refs['content'];
    content.getDOMNode().focus();
  }

  getCloseIconMarkup() {
    return this.props.showCloseIcon
      ? DOM.a({href: '#', className: 'Modal-close', onClick: this.closeModalHandler}, '×')
      : null;
  }

  render() {
    return DOM.div({onClick: this.backgroundClickHandler, className: 'Modal-container', 'data-clickcatcher': true},
      DOM.div({ref: 'content', className: 'Modal-content', tabIndex: -1, onKeyDown: this.keyDownHandler},
        DOM.div({className: 'Modal-header'},
          DOM.h3({className: 'Modal-title'}, this.props.title),
          this.getCloseIconMarkup()
        ),
        DOM.div({className: 'Modal-body'},
          this.props.children
        )
      )
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

module Modal {
  export interface Props extends ModalProps {}
}

export = Modal;
