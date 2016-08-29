import * as React from 'react';

interface ErrorProps {
  message: string;
}

const Error: React.StatelessComponent<ErrorProps> = ({ message }) => (
  <tr className='details-row error'>
    <td colSpan={6}>{message}</td>
  </tr>
);

export default Error;
