import * as React from 'react';

interface ChunkTableRowProps {
  leftLineContent: any;
  leftLineType: string;
  rightLineContent: any;
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

export default ChunkTableRow;
