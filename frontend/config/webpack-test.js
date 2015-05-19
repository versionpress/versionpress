'use strict';

require('core-js/es5');

var context = require.context('../src', true, /__tests__\/.*\.ts$/);
context.keys().forEach(context);
