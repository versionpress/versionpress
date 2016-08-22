import * as React from 'react';
import { Link } from 'react-router';

import config from '../../config';

const routes = config.routes;

interface FooterProps {
  pages: number[];
}

const Footer: React.StatelessComponent<FooterProps> = ({ pages }) => (
  <tfoot>
    <tr>
      <td className='vp-table-pagination' colSpan={6}>
        {pages.map((page: number) => (
          <Link
            activeClassName='active'
            to={page === 1 ? routes.home : routes.page}
            params={page === 1 ? null : { page: page }}
            key={page}
          >
            {page}
          </Link>
        ))}
      </td>
    </tr>
  </tfoot>
);

export default Footer;
