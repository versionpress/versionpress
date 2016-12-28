import * as React from 'react';
import { observer } from 'mobx-react';

interface TitleProps {
  title?: React.ReactNode;
}

const Title: React.StatelessComponent<TitleProps> = ({ title }) => (
  <h3 className='Modal-title'>
    {title}
  </h3>
);

export default observer(Title);
