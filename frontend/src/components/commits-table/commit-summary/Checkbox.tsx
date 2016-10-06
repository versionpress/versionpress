import * as React from 'react';
import { observer } from 'mobx-react';

interface CheckboxProps {
  isVisible: boolean;
  isChecked: boolean;
  isDisabled: boolean;
  onClick(e: React.MouseEvent): void;
}

const Checkbox: React.StatelessComponent<CheckboxProps> = (props) => {
  const {
    isVisible,
    isChecked,
    isDisabled,
    onClick,
  } = props;

  return isVisible
    ? <td className='column-cb' onClick={onClick}>
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
