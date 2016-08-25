import * as React from 'react';

interface CheckboxProps {
  canUndo: boolean;
  isChecked: boolean;
  isDisabled: boolean;
  onClick(e: React.MouseEvent): void;
}

const Checkbox: React.StatelessComponent<CheckboxProps> = (props) => {
  const {
    canUndo,
    isChecked,
    isDisabled,
    onClick,
  } = props;

  return canUndo
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

export default Checkbox;
