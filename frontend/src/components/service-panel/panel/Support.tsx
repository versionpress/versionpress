import * as React from 'react';

import config from '../../../config/config';

const Support: React.StatelessComponent<{}> = () => (
  <div>
    <h3>Community and support</h3>
    <ul>
      <li>
        Having trouble using VersionPress?
        Our <a href='http://docs.versionpress.net'>documentation</a> has you covered.
      </li>
      <li>
        Can’t find what you need?
        Please visit our <a href='https://github.com/versionpress/support'>support&nbsp;repository</a>.
      </li>
      <li>
        <a href={`${config.api.adminUrl}admin.php?page=versionpress/admin/system-info.php`}>
          System information
        </a> page.
      </li>
    </ul>
  </div>
);

export default Support;
