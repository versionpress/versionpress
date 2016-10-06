import * as React from 'react';

const ChunkSeparator: React.StatelessComponent<{}> = () => (
  <table className='chunk-separator'>
    <tbody>
      <tr className='line'>
        <td className='line-left'><span className='hellip'>&middot;&middot;&middot;</span></td>
        <td className='line-separator' />
        <td className='line-right'><span className='hellip'>&middot;&middot;&middot;</span></td>
      </tr>
      <tr className='line'>
        <td className='line-left' />
        <td className='line-separator' />
        <td className='line-right' />
      </tr>
    </tbody>
  </table>
);

export default ChunkSeparator;
