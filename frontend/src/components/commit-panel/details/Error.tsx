import * as React from 'react';

interface ErrorProps {
  error: string;
}

const Error: React.StatelessComponent<ErrorProps> = ({ error }) => (
  <div className='CommitPanel-error'>
    <p>{error}</p>
  </div>
);

export default Error;
