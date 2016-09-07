import * as React from 'react';

interface DetailsProps {
  text: string;
}

const Details: React.StatelessComponent<DetailsProps> = ({ text }) => (
  <p className='FlashMessage-details'>
    {text}
  </p>
);

export default Details;
