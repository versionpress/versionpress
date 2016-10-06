import * as React from 'react';
import { observer } from 'mobx-react';

import DetailsLevel from '../../../enums/DetailsLevel';

interface ButtonsProps {
  detailsLevel: DetailsLevel;
  onDetailsLevelChange(detailsLevel: DetailsLevel): void;
}

const Buttons: React.StatelessComponent<ButtonsProps> = ({ detailsLevel, onDetailsLevelChange }) => {
  return (
    <div className='CommitPanel-details-buttons'>
      <button
        className='button'
        disabled={detailsLevel === DetailsLevel.Overview}
        onClick={() => onDetailsLevelChange(DetailsLevel.Overview)}
      >
        Overview
      </button>
      <button
        className='button'
        disabled={detailsLevel === DetailsLevel.FullDiff}
        onClick={() => onDetailsLevelChange(DetailsLevel.FullDiff)}
      >
        Full diff
      </button>
    </div>
  );
};

export default observer(Buttons);
