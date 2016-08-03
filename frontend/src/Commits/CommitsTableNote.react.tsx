import * as React from 'react';

interface CommitsTableNoteProps extends React.Props<JSX.Element> {
  message: string;
}

export default class CommitsTableNote extends React.Component<CommitsTableNoteProps, {}>  {

  render() {
    const { message } = this.props;

    return (
      <tr className='note'>
        <td colSpan={6}>{message}</td>
      </tr>
    );
  }

}
