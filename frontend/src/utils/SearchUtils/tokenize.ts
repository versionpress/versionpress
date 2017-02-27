/// <reference path='../../components/search/Search.d.ts' />

import Token from '../../entities/Token';

let counter;

export function tokenize(text: string, config: SearchConfig): Token[] {
  let tokens: Token[] = [];
  let mem = '';

  counter = 0;

  for (let i = 0; i <= text.length; i++) {
    const character = text[i];

    if (character === ' ' || !character) {
      if (mem) {
        tokens.push(new Token(mem, config, counter++));
      }
      if (character) {
        tokens.push(new Token(null, config, counter++));
      }
      mem = '';
    } else if (character === '\'' || character === '"') {
      mem += character;
      while (text.length > i + 1 && text[i + 1] !== character) {
        i += 1;
        mem += text[i];
      }
      if (text.length > i + 1 && text[i + 1] === character) {
        i += 1;
        mem += character;
      }
    } else if (character !== ' ') {
      mem += character;
    }

    if (character === ':' && text.length > i + 1 && text[i + 1] === ' ') {
      mem += ' ';
      i += 1;
    }
  }
  return tokens;
}
