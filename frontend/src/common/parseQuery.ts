/**
 * @param query Search query to parse
 */
export default function parseQuery(query: string) {

  // Regularize white spacing
  // Make in-between white spaces a unique space
  query = query.trim().replace(/s+/g, ' ');

  // https://regex101.com/r/wT6zG3/2
  const regex = /(-)?(?:(\S+):)?(?:'((?:[^'\\]|\\.)*)'|"((?:[^"\\]|\\.)*)"|(\S+))/g;
  let term;
  let terms = {};

  while((term = regex.exec(query)) !== null) {
    if (term !== regex.lastIndex) {
      regex.lastIndex++;
    }

    const key = (term[2] || 'text').toLowerCase();

    terms[key] = terms[key] || [];

    terms[key].push({
      n: term[1] === '-',
      s: term[3] || term[4] || term[5]
    });
  }

  return terms;

}
