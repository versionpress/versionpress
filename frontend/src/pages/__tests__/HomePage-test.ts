/// <reference path='../../../typings/typings.d.ts' />

import React = require('react/addons');
import HomePage = require('../HomePage.react');

const expect = chai.expect;

describe('HomePage', () => {
  it('displays the title', () => {
    const testUtils = React.addons.TestUtils;

    const homepage = testUtils.renderIntoDocument(
      React.createElement(HomePage)
    );

    const heading = testUtils.findRenderedDOMComponentWithTag(homepage, 'h1').getDOMNode();

    expect(heading.textContent).to.equal('Hello world');
  });
});
