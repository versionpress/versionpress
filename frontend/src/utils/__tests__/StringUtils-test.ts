import { expect } from 'chai';

import { getValidVPJSON } from '../StringUtils';

describe('getValidVPJSON', () => {
  it('correctly returns valid JSON', () => {
    const objects = [
      '{"__VP__": true}',
      '{"hello": "world", "__VP__": true, "test": 2}',
      '{"__VP__": true, "nested": {"hello": "world", "test": 2}}',
    ];
    objects.forEach((item) => expect(getValidVPJSON(item)).to.equal(item));
  });

  it('returns null when __VP__ is not present', () => {
    const objects = [
      '{"hello": "world"}',
      '{"VP": true}',
    ];
    objects.forEach((item) => expect(getValidVPJSON(item)).to.equal(null));
  });

  it('correctly returns valid JSON with nested __VP__', () => {
    const objects = [
      '{"test": true, "data": {"__VP__": true}}',
      '{"hello": "world", "test": {"__VP__": true, "hello": "world"}}',
    ];
    objects.forEach((item) => expect(getValidVPJSON(item)).to.equal(item));
  });

  it('handles quotes and backslashes correctly', () => {
    const objects = [
      '{"hell{o": "wo{rld", "__VP__": true, "te}st": 2}',
      '{"he\\"ll{\\\\\\"o": "wo{rld\\\\", "__VP__": true, "te}st": 2}',
    ];
    objects.forEach((item) => expect(getValidVPJSON(item)).to.equal(item));
  });

  it('works with any string around the valid JSON', () => {
    const objects = [
      ['     {"__VP__": true}     ', '{"__VP__": true}'],
      ['{} {"__VP__": true} }{}', '{"__VP__": true}'],
      ['"{"__VP__": true}"', '{"__VP__": true}'],
      ['{"hello": "world", {"__VP__": true}', '{"__VP__": true}'],
      ['{"test": "object"} {"__VP__": true}}', '{"__VP__": true}'],
      [
        '{"a": 1, {"hello": "world", "test": {"__VP__": true, "hello": "world"}}}',
        '{"hello": "world", "test": {"__VP__": true, "hello": "world"}}', // Max 2 levels deep
      ],
    ];
    objects.forEach((item) => expect(getValidVPJSON(item[0])).to.equal(item[1]));
  });

  it('returns null when it is not possible to match the braces', () => {
    const objects = [
      '{"test": true, "data": {"__VP__": true',
      '{"hello": "world"} "__VP__": true, "hello": "world"}',
    ];
    objects.forEach((item) => expect(getValidVPJSON(item)).to.equal(null));
  });
});
