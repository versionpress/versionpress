/// <reference path='../../../typings/tsd.d.ts' />
/// <reference path='../Commits.d.ts' />

import React = require('react/addons');
import CommitsTable = require('../CommitsTable.react');
import CommitsTableRow = require('../CommitsTableRow.react');
import utils = require('../../common/__tests__/utils');

describe('CommitsTable', () => {
  var onUndo: Sinon.SinonSpy;
  var onRollback: Sinon.SinonSpy;
  var props: CommitsTable.Props;

  beforeEach((done) => {
    onUndo = sinon.spy();
    onRollback = sinon.spy();
    const commit: Commit = {
      message: 'Test message',
      date: '2015-01-01T01:23:34',
      hash: 'abcdef',
      canUndo: true,
      canRollback: true,
      isEnabled: true,
      isInitial: false,
      changes: []
    };
    props = {
      currentPage: 1,
      pages: [1, 2, 3, 4, 5],
      commits: [commit, commit],
      onUndo: onUndo,
      onRollback: onRollback,
      diffProvider: {getDiff: (hash) => new Promise<string>(() => '')}
    };
    done();
  });

  it('renders correctly', () => {
    const component = utils.render(React.createElement(CommitsTable, props));
    expect(component.type).to.equal('table');
    expect(component.props.className).to.contain('vp-table');
  });

  it('should render the commits', () => {
    const component = utils.render(React.createElement(CommitsTable, props));

    function getChildrenByType(component, type, depth) {
      return utils.getChildren(component, depth).filter(c => c.type === type);
    }

    expect(getChildrenByType(component, CommitsTableRow, 2).length).to.equal(props.commits.length);
  });

  it('should render the pagination correctly', () => {
    const component = utils.render(React.createElement(CommitsTable, props));
    const pagination = utils.getChildrenByClass(component, 'vp-table-pagination', 2);
    expect(pagination.length).to.equal(1);
    expect(pagination[0].props.children.length).to.equal(props.pages.length);
  });

});
