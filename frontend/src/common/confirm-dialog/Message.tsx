import * as React from 'react';

interface MessageProps {
  message: React.ReactNode;
}

const Message: React.StatelessComponent<MessageProps> = ({ message }) => (
  <div className='ConfirmDialog-message'>
    {message}
  </div>
);

export default Message;
