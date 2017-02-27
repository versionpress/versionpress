/// <reference path='../../components/search/Search.d.ts' />

export function getMatch(subString: string, array: any[], key: string = null) {
  return array
    .filter(item => {
      const value: string = key ? item[key] : item;
      return contains(value, subString) && subString.length < value.length;
    })
    .sort((a, b) => {
      return key
        ? a[key].length - b[key].length
        : a.length - b.length;
    });
}

function contains(value: string, subString: string) {
  return value.toLowerCase().indexOf(subString.toLowerCase()) > -1;
}
