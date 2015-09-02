export function verbToPastTense(verb: string) {
  return verb + (verb.slice(-1) === 'e' ? 'd' : 'ed');
}

export function capitalize(word: string) {
  return word.charAt(0).toUpperCase() + word.slice(1);
}
