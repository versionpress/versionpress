import * as React from 'react';

interface UpdateNoticeProps {
  onClick(e: React.MouseEvent): void;
}

const UpdateNotice: React.StatelessComponent<UpdateNoticeProps> = ({ onClick }) => (
  <div className='updateNotice'>
    <span>There are newer changes available.</span>
    <a href='#' onClick={onClick}>Refresh now.</a>
  </div>
);

export default UpdateNotice;
