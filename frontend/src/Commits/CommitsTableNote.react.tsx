/// <reference path='../../typings/typings.d.ts' />

import * as React from 'react';

interface CommitsTableNoteProps extends React.Props<JSX.Element> {
  message: string;
}

export default class CommitsTableNote extends React.Component<CommitsTableNoteProps, {}>  {

  render() {
    return (
      <tr className='note'>
        <td colSpan={5}>{this.props.message}</td>
      </tr>
    );
  }

}
