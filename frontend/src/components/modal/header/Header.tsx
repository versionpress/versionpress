import * as React from 'react';
import { observer } from 'mobx-react';

import CloseIcon from './CloseIcon';
import Title from './Title';

interface HeaderProps {
  title?: React.ReactNode;
  showCloseIcon: boolean;
  onCloseClick(): void;
}

const Header: React.StatelessComponent<HeaderProps> = ({ title, showCloseIcon, onCloseClick }) => (
  <div className='Modal-header'>
    <Title title={title} />
    {showCloseIcon &&
      <CloseIcon onClick={onCloseClick} />
    }
  </div>
);

export default observer(Header);
