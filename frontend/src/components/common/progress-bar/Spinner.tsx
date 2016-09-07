import * as React from 'react';
import * as classNames from 'classnames';

interface SpinnerProps {
  isVisible: boolean;
}

const Spinner: React.StatelessComponent<SpinnerProps> = ({ isVisible }) => {
  const spinnerClassName = classNames({
    'ProgressBar-spinner': true,
    'hide': !isVisible,
  });

  return (
    <div className={spinnerClassName} />
  );
};

export default Spinner;
