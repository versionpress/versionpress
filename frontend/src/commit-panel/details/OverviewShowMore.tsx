import * as React from 'react';

interface OverviewShowMoreProps {
  displayNumber: number;
  onClick(e: React.MouseEvent): void;
}

const OverviewShowMore: React.StatelessComponent<OverviewShowMoreProps> = ({ displayNumber, onClick }) => (
  <li>
    <a onClick={onClick}>
      show {displayNumber} more...
    </a>
  </li>
);

export default OverviewShowMore;
