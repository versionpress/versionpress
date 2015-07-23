/// <reference path='../../../typings/tsd.d.ts' />

import React = require('react/addons');
import ReactRouterStub = require('../../common/__tests__/ReactRouterStub');
import ReactContextStub = require('../../common/__tests__/ReactContextStub');
import HomePage = require('../HomePage.react');
import utils = require('../../common/__tests__/utils');
import CommitsTable = require('../../Commits/CommitsTable.react');

const testUtils = React.addons.TestUtils;

const fakeData = {
  pages: [0, 1, 2],
  commits: [
    {hash: 'fake1', message: 'Fake message 1', canUndo: true, canRollback: false, isEnabled: true, date: '2015-01-09T01:23:34'},
    {hash: 'fake2', message: 'Fake message 2', canUndo: true, canRollback: true, isEnabled: true, date: '2015-01-06T01:23:34'},
    {hash: 'fake3', message: 'Activated VersionPress', canUndo: true, canRollback: true, isEnabled: true, date: '2015-01-03T01:23:34'},
    {hash: 'fake4', message: 'Fake message 4', canUndo: false, canRollback: false, isEnabled: false, date: '2015-01-02T01:23:34'},
    {hash: 'fake5', message: 'Fake message 5', canUndo: false, canRollback: false, isEnabled: false, date: '2015-01-01T01:23:34'}
  ]
};

describe('HomePage', () => {
  var props: HomePage.Props;
  var fakeServer: Sinon.SinonFakeServer;
  var routerStub: Sinon.SinonSpy;
  var stubContext;

  beforeEach((done) => {
    fakeServer = sinon.fakeServer.create();
    stubContext = ReactContextStub;
    routerStub = sinon.spy();
    props = {
      router: ReactRouterStub,
      params: {
        page: '2'
      }
    };
    done();
  });

  afterEach((done) => {
    fakeServer.restore();
    done();
  });

  it('renders correctly', () => {
    const handler = stubContext(HomePage, {router: utils.functionize(ReactRouterStub)});
    const component = <React.DOMComponent<React.HTMLAttributes>> testUtils.renderIntoDocument(
      React.createElement(handler, props)
    );
    fakeServer.requests[0].respond(200, { 'Content-Type': 'application/json' }, JSON.stringify(fakeData));

    const commitsTable = testUtils.findRenderedComponentWithType(component, CommitsTable);
    expect(commitsTable.props.pages.length).to.equal(fakeData.pages.length);
    expect(commitsTable.props.commits.length).to.equal(fakeData.commits.length);
    expect(commitsTable.props.currentPage).to.equal(parseInt(props.params.page, 10));

    const comp: any = component.getDOMNode();
    expect(comp.className).not.to.contain('loading');
  });
});

