import * as React from 'react';
import { observer } from 'mobx-react';

import DetailsLevel from '../../../enums/DetailsLevel';

interface DetailsLevelButtonsProps {
  detailsLevel: DetailsLevel;
  onDetailsLevelChange(detailsLevel: DetailsLevel): void;
}

const DetailsLevelButtons: React.StatelessComponent<DetailsLevelButtonsProps> = ({ detailsLevel, onDetailsLevelChange }) => (
  <div className='detail-buttons'>
    <button
      className='button'
      disabled={detailsLevel === DetailsLevel.Overview}
      onClick={e => { e.stopPropagation(); onDetailsLevelChange(DetailsLevel.Overview); }}
    >
      Overview
    </button>
    <button
      className='button'
      disabled={detailsLevel === DetailsLevel.FullDiff}
      onClick={e => { e.stopPropagation(); onDetailsLevelChange(DetailsLevel.FullDiff); }}
    >
      Full diff
    </button>
  </div>
);

export default observer(DetailsLevelButtons);
