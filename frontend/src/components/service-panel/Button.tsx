import * as React from 'react';

import './Button.less';

interface ButtonProps {
  onClick(): void;
}

const Button: React.StatelessComponent<ButtonProps> = ({ onClick }) => (
  <button
    className='ServicePanelButton'
    onClick={onClick}
    style={{ order: 1 }}
  >
    <span className='icon vp-icon-cog' />
  </button>
);

export default Button;
