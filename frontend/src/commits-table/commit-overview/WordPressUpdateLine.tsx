import * as React from 'react';

interface WordPressUpdateLineProps {
  version: string;
}

const WordPressUpdateLine: React.StatelessComponent<WordPressUpdateLineProps> = ({ version }) => (
  <span>
    Updated <span className='identifier'>WordPress</span>
    {' '} to version <span className='identifier'>{version}</span>
  </span>
);

export default WordPressUpdateLine;
