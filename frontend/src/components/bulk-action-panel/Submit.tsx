import * as React from 'react';
import { observer } from 'mobx-react';

interface SubmitProps {
  isDisabled: boolean;
  onSubmit(): void;
}

const Submit: React.StatelessComponent<SubmitProps> = ({ isDisabled, onSubmit }) => (
  <input
    type='submit'
    id='BulkActionPanel-doaction'
    className='button action'
    value='Apply'
    onClick={e => { e.preventDefault(); onSubmit(); }}
    disabled={isDisabled}
  />
);

export default observer(Submit);
