/// <reference path='../../../typings/tsd.d.ts' />

import React = require('react/addons');
import HomePage = require('../HomePage.react');

const expect = chai.expect;

describe('HomePage', () => {
  it('displays the title', () => {
    const TestUtils = React.addons.TestUtils;

    const homepage = TestUtils.renderIntoDocument(
      React.createElement(HomePage)
    );

    const heading = TestUtils.findRenderedDOMComponentWithTag(homepage, 'h1').getDOMNode();

    expect(heading.textContent).to.equal('Hello world');
  });
});
