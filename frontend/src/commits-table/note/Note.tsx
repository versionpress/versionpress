import * as React from 'react';

interface NoteProps {
  children?: React.Children;
}

const Note: React.StatelessComponent<NoteProps> = ({ children }) => (
  <tbody>
    <tr className='note'>
      <td colSpan={6}>
        {children}
      </td>
    </tr>
  </tbody>
);

export default Note;
