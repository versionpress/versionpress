import ArrayUtils = require('./ArrayUtils');

export function verbToPastTense(verb: string) {
  if (verb.match(/.*[aeiouy]y$/)) {
    return verb + 'ed';
  }

  if (verb.slice(-1) === 'y') {
    return verb.substr(0, verb.length - 1) + 'ied';
  }

  return verb + (verb.slice(-1) === 'e' ? 'd' : 'ed');
}

export function capitalize(word: string) {
  return word.charAt(0).toUpperCase() + word.slice(1);
}


export function join(array: any[], separator: string = ', ' , lastSeparator: string = ' and ') {
  return ArrayUtils.interspace(array, separator, lastSeparator).join('');
}