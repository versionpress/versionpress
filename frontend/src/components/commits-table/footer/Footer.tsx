import * as React from 'react';
import { observer } from 'mobx-react';
import { IndexLink, Link } from 'react-router';

import config from '../../../config/config';

const routes = config.routes;

interface FooterProps {
  pages: number[];
}

const Footer: React.StatelessComponent<FooterProps> = ({ pages }) => (
  <div className="vp-table-footer" style={{ flex: '1 0 100%' }}>
    <div className='vp-table-pagination'>
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
    </div>
  </div>
);

export default observer(Footer);
