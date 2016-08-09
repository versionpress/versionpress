import * as React from 'react';

interface SubmitProps {
  isDisabled: boolean;
  onClick(e: React.MouseEvent): void;
}

const Submit: React.StatelessComponent<SubmitProps> = ({ isDisabled, onClick }) => (
  <input
    type="submit"
    id="BulkActionPanel-doaction"
    className="button action"
    value="Apply"
    onClick={onClick}
    disabled={isDisabled}
  />
);

export default Submit;
