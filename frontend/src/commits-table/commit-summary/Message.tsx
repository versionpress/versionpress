import * as React from 'react';

import DetailsLevelButtons from './DetailsLevelButtons';
import MergeIcon from './MergeIcon';
import DetailsLevel from '../../enums/DetailsLevel';

interface MessageProps {
  commit: Commit;
  detailsLevel: DetailsLevel;
  onDetailsLevelClick(e: React.MouseEvent, detailsLevel: DetailsLevel): void;
}

const Message: React.StatelessComponent<MessageProps> = ({ commit, detailsLevel, onDetailsLevelClick }) => (
  <td className='column-message'>
    {commit.isMerge &&
      <MergeIcon />
    }
    {renderMessage(commit.message)}
    {detailsLevel !== DetailsLevel.None &&
      <DetailsLevelButtons
        detailsLevel={detailsLevel}
        onButtonClick={onDetailsLevelClick}
      />
    }
  </td>
);

function renderMessage(message: string) {
  const messageChunks = /(.*)'(.*)'(.*)/.exec(message);

  if (!messageChunks || messageChunks.length < 4) {
    return <span>{message}</span>;
  }

  return (
    <span>
      {messageChunks[1] !== '' && renderMessage(messageChunks[1])}
      {messageChunks[2] !== '' && <span className='identifier'>{messageChunks[2]}</span>}
      {messageChunks[3] !== '' && renderMessage(messageChunks[3])}
    </span>
  );
}

export default Message;
