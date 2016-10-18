import * as React from 'react';
import { observer } from 'mobx-react';

interface ShowMoreProps {
  displayNumber: number;
  onClick(): void;
}

const ShowMore: React.StatelessComponent<ShowMoreProps> = ({ displayNumber, onClick }) => (
  <li>
    <a onClick={e => { e.preventDefault(); onClick(); }}>
      show {displayNumber} more...
    </a>
  </li>
);

export default observer(ShowMore);
