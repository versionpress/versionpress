import * as React from 'react';

const NotAbleNote: React.StatelessComponent<{}> = () => (
  <tbody>
    <tr className='note'>
      <td colSpan={6}>
        VersionPress is not able to undo changes made before it has been activated.
      </td>
    </tr>
  </tbody>
);

export default NotAbleNote;
