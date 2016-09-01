/// <reference path='../Search.d.ts' />

interface Adapter {
  isValueValid(value: string): boolean;
  serialize(item: SearchConfigItemContent): string;
  deserialize(value: string): SearchConfigItemContent | string;
}
