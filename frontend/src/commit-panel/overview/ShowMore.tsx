import * as React from 'react';

interface ShowMoreProps {
  displayNumber: number;
  onClick(e: React.MouseEvent): void;
}

const ShowMore: React.StatelessComponent<ShowMoreProps> = ({ displayNumber, onClick }) => (
  <li>
    <a onClick={onClick}>
      show {displayNumber} more...
    </a>
  </li>
);

export default ShowMore;
