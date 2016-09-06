/// <reference path='../common/Commits.d.ts' />

export function indexOf(array: Commit[], commit: Commit) {
  for (let i = 0; i < array.length; i++) {
    if (commit.hash === array[i].hash) {
      return i;
    }
  }
  return -1;
}
