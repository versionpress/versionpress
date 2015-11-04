/// <reference path='../../typings/typings.d.ts' />

import React = require('react');

require('./WelcomePanel.less');

const DOM = React.DOM;

interface WelcomePanelProps {
  onHide: React.MouseEventHandler;
}

class WelcomePanel extends React.Component<WelcomePanelProps, {}> {

  render() {
    return DOM.div({className: 'WelcomePanel welcome-panel'},
      DOM.a({
        className: 'welcome-panel-close',
        href: '#',
        onClick: this.props.onHide
      }, 'Dismiss'),
      DOM.div({className: 'welcome-panel-content'},
        DOM.h3(null, 'Welcome!'),
        DOM.p({className: 'about-description'},
          'Below is the main VersionPress table which will grow as changes are made to this site. You can ',
          DOM.strong(null, 'Undo'), ' specific changes from the history or ',
          DOM.strong(null, 'Roll back'), ' the site entirely to a previous state.'
        )
      )
    );
  }

}

module WelcomePanel {
  export interface Props extends WelcomePanelProps {}
}

export = WelcomePanel;
