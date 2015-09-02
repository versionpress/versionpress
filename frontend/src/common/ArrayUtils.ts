/**
 * Groups items in array by keys returned from keyExtractorFn.
 * The extractor can return either one key (string, number,...) or array of keys.
 * In case of array it returns nested object with keys as properties.
 *
 * Example:
 * array: [
 *   {name: 'John', surname: 'Doe', age: 23},
 *   {name: 'Jack', surname: 'Black', age: 34},
 *   {name: 'John', surname: 'Doe', age: 45},
 *   {name: 'Charles', surname: 'Doe', age: 56},
 *  ]
 * keyExtractorFn: (man) => [man.surname, man.name]
 *
 * Result:
 * {
 *   'Doe': {
 *     'Charles': [ {name: 'Charles', surname: 'Doe', age: 56} ],
 *     'John': [ {name: 'John', surname: 'Doe', age: 23}, {name: 'John', surname: 'Doe', age: 45} ],
 *   },
 *   'Black': {
 *     'Jack': [ {name: 'Jack', surname: 'Black', age: 34} ]
 *   }
 * }
 *
 */
export function groupBy<T>(array: T[], keyExtractorFn: (T) => string|string[]) {
  var result = {};
  array.forEach(item => {
    let keys: any = keyExtractorFn(item);
    if (typeof(keys) === 'string') {
      keys = [keys];
    }

    let target: any = result;
    let lastKey = keys[keys.length - 1];
    keys.forEach(key => {
      if (typeof(target[key]) === 'undefined') {
        target[key] = key === lastKey ? [] : {};
      }
      target = target[key];
    });

    target.push(item);
  });

  return result;
}

/**
 * Returns new array with separator between every two items of input array.
 */
export function interspace(array: any[], separator: any) {
  if (array.length === 0) {
    return [];
  }

  return array.slice(1).reduce(function(xs, x, i) {
    return xs.concat([separator, x]);
  }, [array[0]]);
}

/**
 * Removes duplicates from array. Hash function should return unique string
 * for every item.
 *
 */
export function filterDuplicates<T>(array: T[], hashFn: (T) => string) {
  let uniqueHashSet = [];

  return array.filter(item => {
    let hash = hashFn(item);
    if (uniqueHashSet.indexOf(hash) === -1) {
      uniqueHashSet.push(hash);
      return true;
    }
    return false;
  });
}

/**
 * Counts duplicates of objects in array. Uses `groupBy` function and have similar
 * output. Only instead of arrays with grouped objects contains count of these objects.
 */
export function countDuplicates<T>(array: T[], fn: (T) => any|any[]) {
  let groupedChanges = groupBy(array, fn);

  function countNestedArrays(arr) {
    for (let key in arr) {
      if (Array.isArray(arr[key])) {
        arr[key] = arr[key].length;
      } else {
        countNestedArrays(arr[key]);
      }
    }
  }

  countNestedArrays(groupedChanges);

  return groupedChanges;
}
