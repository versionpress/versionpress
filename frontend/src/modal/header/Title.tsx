import * as React from 'react';

interface TitleProps {
  title?: React.ReactNode;
}

const Title: React.StatelessComponent<TitleProps> = ({ title }) => (
  <h3 className='Modal-title'>
    {title}
  </h3>
);

export default Title;
