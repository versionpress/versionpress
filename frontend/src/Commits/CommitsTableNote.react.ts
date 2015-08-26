/// <reference path='../../typings/tsd.d.ts' />

import React = require('react');

const DOM = React.DOM;

interface CommitsTableNoteProps {
  message: string;
}

class CommitsTableNote extends React.Component<CommitsTableNoteProps, any>  {

  render() {
    return DOM.tr({className: 'note'},
      DOM.td({colSpan: 3}, this.props.message)
    );
  }

}

module CommitsTableNote {
  export interface Props extends CommitsTableNoteProps {}
}

export = CommitsTableNote;
