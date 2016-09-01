/// <reference path='../Search.d.ts' />

export function getMatch(subString: string, array: SearchConfigItemContent[], key: string) {
  return array
    .filter(item => {
      const value: string = item[key];
      return contains(value, subString) && subString.length < value.length;
    })
    .sort((a, b) => a[key].length - b[key].length );
}

function contains(value: string, subString: string) {
  return value.toLowerCase().indexOf(subString.toLowerCase()) > -1;
}
