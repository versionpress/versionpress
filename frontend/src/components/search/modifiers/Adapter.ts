import Token from '../../../entities/Token';

interface Adapter {
  getDefaultHint(): string;
  getHints(token: Token): SearchConfigItemContent[];
  isValueValid(value: string): boolean;
  serialize(item: any): string;
  deserialize(value: string): SearchConfigItemContent | string;
}
export default Adapter;
