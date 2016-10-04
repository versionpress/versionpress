import * as React from 'react';

interface WordPressUpdateProps {
  version: string;
}

const WordPressUpdate: React.StatelessComponent<WordPressUpdateProps> = ({ version }) => (
  <span>
    Updated <span className='identifier'>WordPress</span>
    {' '} to version <span className='identifier'>{version}</span>
  </span>
);

export default WordPressUpdate;
