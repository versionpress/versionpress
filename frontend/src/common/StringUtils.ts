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
    '$'                     : 's'
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

export function getValidVPJSON(str: string) {
  const index = str.indexOf('__VP__');
  const len = str.length;

  function findFirstFreeQuote(openBrackets, from, dir, cond: (i: number, openBrackets: number) => boolean) {
    let inQuotes = false;
    let backslash = false;
    let i;

    for (i = from; cond(i, openBrackets); i += dir) {
      backslash = i > 0 && str[i - 1] === '\\'
        ? !backslash
        : false;
      if (backslash) {
        continue;
      }

      if (str[i] === '\"') {
        inQuotes = !inQuotes;
        continue;
      }

      if (!inQuotes) {
        if (str[i] === '{') {
          openBrackets += 1;
        } else if (str[i] === '}') {
          openBrackets -= 1;
        }
      }
    }

    if (openBrackets !== 0) {
      return null;
    }
    return i;
  }

  const start = findFirstFreeQuote(-1, index - 2, -1, (i, openBrackets) => openBrackets < 0 && i >= 0);
  const end = findFirstFreeQuote(1, index + 7, 1, (i, openBrackets) => openBrackets > 0 && i < len);

  if (start === null || end === null) {
    return null;
  }

  const start2 = findFirstFreeQuote(-1, start - 1, -1, (i, openBrackets) => openBrackets < 0 && i >= 0);
  const end2 = findFirstFreeQuote(1, end, 1, (i, openBrackets) => openBrackets > 0 && i < len);

  if (start2 === null || end2 === null) {
    return str.substring(start + 1, end);
  }

  return str.substring(start2 + 1, end2);
}
