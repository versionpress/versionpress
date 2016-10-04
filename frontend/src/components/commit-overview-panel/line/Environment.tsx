import * as React from 'react';

interface EnvironmentProps {
  environment: string;
}

const Environment: React.StatelessComponent<EnvironmentProps> = ({ environment }) => (
  <li className='environment'>
    <em>{`Environment: ${environment}`}</em>
  </li>
);

export default Environment;
