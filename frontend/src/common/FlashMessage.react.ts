/// <reference path='../../typings/typings.d.ts' />

import React = require('react');

require('./FlashMessage.less');

const DOM = React.DOM;

interface FlashMessageProps {
  code: string;
  message: string;
}

class FlashMessage extends React.Component<FlashMessageProps, {}> {

  render() {
    if (this.props.code === null) {
      return null;
    }

    return DOM.div({className: this.props.code},
      DOM.p(null, this.props.message)
    );
  }

}

module FlashMessage {
  export interface Props extends FlashMessageProps {}
}

export = FlashMessage;
