'use strict';

require('core-js');

var context = require.context('../src', true, /__tests__\/.*\.ts$/);
context.keys().forEach(context);
