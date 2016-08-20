import * as React from 'react';

interface NoteProps {
  message: string;
}

const Note: React.StatelessComponent<NoteProps> = ({ message }) => (
  <tr className='note'>
    <td colSpan={6}>{message}</td>
  </tr>
);

export default Note;
