import * as React from 'react';
import { observer } from 'mobx-react';

interface ErrorProps {
  message: string;
}

const Error: React.StatelessComponent<ErrorProps> = ({ message }) => (
  <div className='details-row error'>
    {message}
  </div>
);

export default observer(Error);
