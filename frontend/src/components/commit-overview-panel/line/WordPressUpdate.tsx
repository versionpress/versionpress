import * as React from 'react';
import { observer } from 'mobx-react';

interface WordPressUpdateProps {
  version: string;
}

const WordPressUpdate: React.StatelessComponent<WordPressUpdateProps> = ({ version }) => (
  <span>
    Updated <span className='identifier'>WordPress</span>
    {' '} to version <span className='identifier'>{version}</span>
  </span>
);

export default observer(WordPressUpdate);
