import * as React from 'react';
import { observer } from 'mobx-react';

interface ChunkTableRowProps {
  leftLineContent: React.ReactNode;
  leftLineType: string;
  rightLineContent: React.ReactNode;
  rightLineType: string;
}

const ChunkTableRow: React.StatelessComponent<ChunkTableRowProps> = (props) => {
  const {
    leftLineContent,
    leftLineType,
    rightLineContent,
    rightLineType,
  } = props;

  return (
    <tr className='line'>
      <td className={`line-left ${leftLineType}`}>
        {leftLineContent}
      </td>
      <td className='line-separator' />
      <td className={`line-right ${rightLineType}`}>
        {rightLineContent}
      </td>
    </tr>
  );
};

export default observer(ChunkTableRow);
