import * as React from 'react';

interface HintProps {
  hint: any;
}

const Hint: React.StatelessComponent<HintProps> = ({ hint }) => (
  <span className='Search-Background-hint'>{hint}</span>
);

export default Hint;
