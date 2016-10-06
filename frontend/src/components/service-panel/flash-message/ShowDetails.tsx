import * as React from 'react';
import * as classNames from 'classnames';
import { observer } from 'mobx-react';

interface ShowDetailsProps {
  isActive: boolean;
  onClick(e: React.MouseEvent): void;
}

const ShowDetails: React.StatelessComponent<ShowDetailsProps> = ({ isActive, onClick }) => {
  const showDetailsClassName = classNames({
    'FlashMessage-detailsLink-displayed': isActive,
    'FlashMessage-detailsLink-hidden': !isActive,
  });

  return (
    <a
      className={showDetailsClassName}
      href='#'
      onClick={onClick}
    >
      Details
    </a>
  );
};

export default observer(ShowDetails);
