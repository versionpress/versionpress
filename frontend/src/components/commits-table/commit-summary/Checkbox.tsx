import * as React from 'react';
import { observer } from 'mobx-react';

interface CheckboxProps {
  isVisible: boolean;
  isChecked: boolean;
  isDisabled: boolean;
  onClick(shiftKey: boolean): void;
}

const Checkbox: React.StatelessComponent<CheckboxProps> = (props) => {
  const {
    isVisible,
    isChecked,
    isDisabled,
    onClick,
  } = props;

  return isVisible
    ? <td className='column-cb' onClick={e => { e.stopPropagation(); onClick(e.shiftKey); }}>
        <input
          type='checkbox'
          checked={isChecked}
          disabled={isDisabled}
          readOnly={true}
        />
      </td>
    : <td className='column-cb' />;
};

export default observer(Checkbox);
