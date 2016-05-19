import * as ArrayUtils from './ArrayUtils';

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

/* Inspired by http://kuwamoto.org/2007/12/17/improved-pluralizing-in-php-actionscript-and-ror/ */
export function pluralize(word: string) {
  const plural = {
    '(quiz)$'               : '$1zes',
    '^(ox)$'                : '$1en',
    '([m|l])ouse$'          : '$1ice',
    '(matr|vert|ind)ix|ex$' : '$1ices',
    '(x|ch|ss|sh)$'         : '$1es',
    '([^aeiouy]|qu)y$'      : '$1ies',
    '(hive)$'               : '$1s',
    '(?:([^f])fe|([lr])f)$' : '$1$2ves',
    '(shea|lea|loa|thie)f$' : '$1ves',
    'sis$'                  : 'ses',
    '([ti])um$'             : '$1a',
    '(tomat|potat|ech|her|vet)o$': '$1oes',
    '(bu)s$'                : '$1ses',
    '(alias)$'              : '$1es',
    '(octop)us$'            : '$1i',
    '(ax|test)is$'          : '$1es',
    '(us)$'                 : '$1es',
    '(meta)$'               : '$1',
    's$'                    : 's',
    '$'                     : 's',
  };

  for (let reg in plural) {
    const pattern = new RegExp(reg, 'i');
    if (pattern.test(word)) {
      return word.replace(pattern, plural[reg]);
    }
  }
}

export function join(array: any[], separator: string = ', ' , lastSeparator: string = ' and ') {
  return ArrayUtils.interspace(array, separator, lastSeparator).join('');
}
