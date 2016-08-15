import * as React from 'react';

interface ConfirmMessageProps {
  message: React.ReactNode;
}

const ConfirmMessage: React.StatelessComponent<ConfirmMessageProps> = ({ message }) => (
  <div className='ConfirmDialog-message'>
    {message}
  </div>
);

export default ConfirmMessage;
