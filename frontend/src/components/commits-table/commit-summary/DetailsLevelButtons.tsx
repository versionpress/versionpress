import * as React from 'react';
import { observer } from 'mobx-react';

import DetailsLevel from '../../../enums/DetailsLevel';

interface DetailsLevelButtonsProps {
  detailsLevel: DetailsLevel;
  onButtonClick(e: React.MouseEvent, detailsLevel: DetailsLevel): void;
}

const DetailsLevelButtons: React.StatelessComponent<DetailsLevelButtonsProps> = ({ detailsLevel, onButtonClick }) => (
  <div className='detail-buttons'>
    <button
      className='button'
      disabled={detailsLevel === DetailsLevel.Overview}
      onClick={e => onButtonClick(e, DetailsLevel.Overview)}
    >
      Overview
    </button>
    <button
      className='button'
      disabled={detailsLevel === DetailsLevel.FullDiff}
      onClick={e => onButtonClick(e, DetailsLevel.FullDiff)}
    >
      Full diff
    </button>
  </div>
);

export default observer(DetailsLevelButtons);
