import * as React from 'react';

interface CloseIconProps {
  onClick(e: React.MouseEvent): void;
}

const CloseIcon: React.StatelessComponent<CloseIconProps> = ({ onClick }) => (
  <a
    href='#'
    className='Modal-close'
    onClick={onClick}
  >
    &times;
  </a>
);

export default CloseIcon;
