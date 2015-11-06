/// <reference path='../../../typings/typings.d.ts' />

import React = require('react/addons');
import NotFoundPage = require('../NotFoundPage.react');
import utils = require('../../common/__tests__/utils');

describe('NotFoundPage', () => {
  it('displays the title', () => {
    const component = utils.render(React.createElement(NotFoundPage));

    expect(component.type).to.equal('div');
    expect(component.props.children).to.deep.equal(
      React.DOM.h1(null, 'Not found.')
    );
  });
});

