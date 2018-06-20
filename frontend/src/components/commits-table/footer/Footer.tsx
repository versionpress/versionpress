import * as React from 'react';
import * as classNames from 'classnames';
import { observer } from 'mobx-react';
import { Link, RouteComponentProps, withRouter } from 'react-router-dom';

import config from '../../../config/config';

const routes = config.routes;

interface FooterProps extends RouteComponentProps<{ page?: string }> {
  pages: number[];
}

const Footer: React.StatelessComponent<FooterProps> = ({
  pages, match: { params: { page: routePage } },
}) => (
  <tfoot>
    <tr>
      <td className='vp-table-pagination' colSpan={6}>
        {pages.map((page: number) => {
          return page === 1
            ? <Link
                className={classNames({ active: !routePage || `${page}` === routePage })}
                to={routes.home}
                key={page}
              >{page}</Link>
            : <Link
                className={classNames({ active: `${page}` === routePage })}
                to={`/${routes.page}/${page}`}
                key={page}
              >{page}</Link>;
        })}
      </td>
    </tr>
  </tfoot>
);

export default withRouter(observer(Footer));
