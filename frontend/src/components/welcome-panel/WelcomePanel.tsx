import * as React from 'react';

import './WelcomePanel.less';

interface WelcomePanelProps {
  onHide(): void;
}

const WelcomePanel: React.StatelessComponent<WelcomePanelProps> = ({ onHide }) => (
  <div className='WelcomePanel welcome-panel'>
    <a
      className='welcome-panel-close'
      href='#'
      onClick={e => { e.preventDefault(); onHide(); }}
    >
      Dismiss
    </a>
    <div className='welcome-panel-content'>
      <h3>Welcome!</h3>
      <p className='about-description'>
        Below is the main VersionPress table which will grow as changes are made to this site.
        You can <strong>Undo</strong> specific changes from the history or <strong>Roll back</strong> the site
        entirely to a previous state.
      </p>
    </div>
  </div>
);

export default WelcomePanel;
