import * as React from 'react';

interface ErrorProps {
  message: any;
}

const Error: React.StatelessComponent<ErrorProps> = ({ message }) => (
  <tr className='details-row error'>
    <td colSpan={6}>{message}</td>
  </tr>
);

export default Error;
