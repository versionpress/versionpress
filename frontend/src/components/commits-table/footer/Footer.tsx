import * as React from 'react';
import { observer } from 'mobx-react';
import { IndexLink, Link } from 'react-router';

import config from '../../../config/config';

const routes = config.routes;

interface FooterProps {
  pages: number[];
}

const Footer: React.StatelessComponent<FooterProps> = ({ pages }) => (
  <tfoot>
    <tr>
      <td className='vp-table-pagination' colSpan={6}>
        {pages.map((page: number) => {
          return page === 1
            ? <IndexLink
                activeClassName='active'
                to={routes.home}
                key={page}
              >{page}</IndexLink>
            : <Link
                activeClassName='active'
                to={`/${routes.page}/${page}`}
                key={page}
              >{page}</Link>;
          })}
      </td>
    </tr>
  </tfoot>
);

export default observer(Footer);
