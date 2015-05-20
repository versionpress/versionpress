interface IteratorResult<T> {
  done: boolean;
  value?: T;
}

interface Iterator<T> {
  next(): IteratorResult<T>;
  return?(value?: any): IteratorResult<T>;
  throw?(e?: any): IteratorResult<T>;
}

interface Iterable<T> {
  [Symbol.iterator](): Iterator<T>;
}

interface IterableIterator<T> extends Iterator<T> {
  [Symbol.iterator](): IterableIterator<T>;
}

interface Symbol {
  /** Returns a string representation of an object. */
  toString(): string;

  /** Returns the primitive value of the specified object. */
  valueOf(): Object;

  [Symbol.toStringTag]: string;
}

interface SymbolConstructor {
  /**
   * A reference to the prototype.
   */
  prototype: Symbol;

  /**
   * Returns a new unique Symbol value.
   * @param  description Description of the new Symbol object.
   */
  (description?: string|number): symbol;

  /**
   * Returns a Symbol object from the global symbol registry matching the given key if found.
   * Otherwise, returns a new symbol with this key.
   * @param key key to search for.
   */
  for(key: string): symbol;

  /**
   * Returns a key from the global symbol registry matching the given Symbol if found.
   * Otherwise, returns a undefined.
   * @param sym Symbol to find the key for.
   */
  keyFor(sym: symbol): string;

  // Well-known Symbols

  /**
   * A method that determines if a constructor object recognizes an object as one of the
   * constructor’s instances. Called by the semantics of the instanceof operator.
   */
  hasInstance: symbol;

  /**
   * A Boolean value that if true indicates that an object should flatten to its array elements
   * by Array.prototype.concat.
   */
  isConcatSpreadable: symbol;

  /**
   * A method that returns the default iterator for an object. Called by the semantics of the
   * for-of statement.
   */
  iterator: symbol;

  /**
   * A regular expression method that matches the regular expression against a string. Called
   * by the String.prototype.match method.
   */
  match: symbol;

  /**
   * A regular expression method that replaces matched substrings of a string. Called by the
   * String.prototype.replace method.
   */
  replace: symbol;

  /**
   * A regular expression method that returns the index within a string that matches the
   * regular expression. Called by the String.prototype.search method.
   */
  search: symbol;

  /**
   * A function valued property that is the constructor function that is used to create
   * derived objects.
   */
  species: symbol;

  /**
   * A regular expression method that splits a string at the indices that match the regular
   * expression. Called by the String.prototype.split method.
   */
  split: symbol;

  /**
   * A method that converts an object to a corresponding primitive value.Called by the ToPrimitive
   * abstract operation.
   */
  toPrimitive: symbol;

  /**
   * A String value that is used in the creation of the default string description of an object.
   * Called by the built-in method Object.prototype.toString.
   */
  toStringTag: symbol;

  /**
   * An Object whose own property names are property names that are excluded from the with
   * environment bindings of the associated objects.
   */
  unscopables: symbol;
}
declare var Symbol: SymbolConstructor;

interface DataView {
  buffer: ArrayBuffer;
  byteLength: number;
  byteOffset: number;
  /**
   * Gets the Float32 value at the specified byte offset from the start of the view. There is
   * no alignment constraint; multi-byte values may be fetched from any offset.
   * @param byteOffset The place in the buffer at which the value should be retrieved.
   */
  getFloat32(byteOffset: number, littleEndian: boolean): number;

  /**
   * Gets the Float64 value at the specified byte offset from the start of the view. There is
   * no alignment constraint; multi-byte values may be fetched from any offset.
   * @param byteOffset The place in the buffer at which the value should be retrieved.
   */
  getFloat64(byteOffset: number, littleEndian: boolean): number;

  /**
   * Gets the Int8 value at the specified byte offset from the start of the view. There is
   * no alignment constraint; multi-byte values may be fetched from any offset.
   * @param byteOffset The place in the buffer at which the value should be retrieved.
   */
  getInt8(byteOffset: number): number;

  /**
   * Gets the Int16 value at the specified byte offset from the start of the view. There is
   * no alignment constraint; multi-byte values may be fetched from any offset.
   * @param byteOffset The place in the buffer at which the value should be retrieved.
   */
  getInt16(byteOffset: number, littleEndian: boolean): number;
  /**
   * Gets the Int32 value at the specified byte offset from the start of the view. There is
   * no alignment constraint; multi-byte values may be fetched from any offset.
   * @param byteOffset The place in the buffer at which the value should be retrieved.
   */
  getInt32(byteOffset: number, littleEndian: boolean): number;

  /**
   * Gets the Uint8 value at the specified byte offset from the start of the view. There is
   * no alignment constraint; multi-byte values may be fetched from any offset.
   * @param byteOffset The place in the buffer at which the value should be retrieved.
   */
  getUint8(byteOffset: number): number;

  /**
   * Gets the Uint16 value at the specified byte offset from the start of the view. There is
   * no alignment constraint; multi-byte values may be fetched from any offset.
   * @param byteOffset The place in the buffer at which the value should be retrieved.
   */
  getUint16(byteOffset: number, littleEndian: boolean): number;

  /**
   * Gets the Uint32 value at the specified byte offset from the start of the view. There is
   * no alignment constraint; multi-byte values may be fetched from any offset.
   * @param byteOffset The place in the buffer at which the value should be retrieved.
   */
  getUint32(byteOffset: number, littleEndian: boolean): number;

  /**
   * Stores an Float32 value at the specified byte offset from the start of the view.
   * @param byteOffset The place in the buffer at which the value should be set.
   * @param value The value to set.
   * @param littleEndian If false or undefined, a big-endian value should be written,
   * otherwise a little-endian value should be written.
   */
  setFloat32(byteOffset: number, value: number, littleEndian: boolean): void;

  /**
   * Stores an Float64 value at the specified byte offset from the start of the view.
   * @param byteOffset The place in the buffer at which the value should be set.
   * @param value The value to set.
   * @param littleEndian If false or undefined, a big-endian value should be written,
   * otherwise a little-endian value should be written.
   */
  setFloat64(byteOffset: number, value: number, littleEndian: boolean): void;

  /**
   * Stores an Int8 value at the specified byte offset from the start of the view.
   * @param byteOffset The place in the buffer at which the value should be set.
   * @param value The value to set.
   */
  setInt8(byteOffset: number, value: number): void;

  /**
   * Stores an Int16 value at the specified byte offset from the start of the view.
   * @param byteOffset The place in the buffer at which the value should be set.
   * @param value The value to set.
   * @param littleEndian If false or undefined, a big-endian value should be written,
   * otherwise a little-endian value should be written.
   */
  setInt16(byteOffset: number, value: number, littleEndian: boolean): void;

  /**
   * Stores an Int32 value at the specified byte offset from the start of the view.
   * @param byteOffset The place in the buffer at which the value should be set.
   * @param value The value to set.
   * @param littleEndian If false or undefined, a big-endian value should be written,
   * otherwise a little-endian value should be written.
   */
  setInt32(byteOffset: number, value: number, littleEndian: boolean): void;

  /**
   * Stores an Uint8 value at the specified byte offset from the start of the view.
   * @param byteOffset The place in the buffer at which the value should be set.
   * @param value The value to set.
   */
  setUint8(byteOffset: number, value: number): void;

  /**
   * Stores an Uint16 value at the specified byte offset from the start of the view.
   * @param byteOffset The place in the buffer at which the value should be set.
   * @param value The value to set.
   * @param littleEndian If false or undefined, a big-endian value should be written,
   * otherwise a little-endian value should be written.
   */
  setUint16(byteOffset: number, value: number, littleEndian: boolean): void;

  /**
   * Stores an Uint32 value at the specified byte offset from the start of the view.
   * @param byteOffset The place in the buffer at which the value should be set.
   * @param value The value to set.
   * @param littleEndian If false or undefined, a big-endian value should be written,
   * otherwise a little-endian value should be written.
   */
  setUint32(byteOffset: number, value: number, littleEndian: boolean): void;

  [Symbol.toStringTag]: string;
}

interface DataViewConstructor {
  new (buffer: ArrayBuffer, byteOffset?: number, byteLength?: number): DataView;
}
declare var DataView: DataViewConstructor;

interface Map<K, V> {
  clear(): void;
  delete(key: K): boolean;
  entries(): IterableIterator<[K, V]>;
  forEach(callbackfn: (value: V, index: K, map: Map<K, V>) => void, thisArg?: any): void;
  get(key: K): V;
  has(key: K): boolean;
  keys(): IterableIterator<K>;
  set(key: K, value?: V): Map<K, V>;
  size: number;
  values(): IterableIterator<V>;
  [Symbol.iterator]():IterableIterator<[K,V]>;
  [Symbol.toStringTag]: string;
}

interface MapConstructor {
  new <K, V>(): Map<K, V>;
  new <K, V>(iterable: Iterable<[K, V]>): Map<K, V>;
  prototype: Map<any, any>;
}
declare var Map: MapConstructor;

interface WeakMap<K, V> {
  clear(): void;
  delete(key: K): boolean;
  get(key: K): V;
  has(key: K): boolean;
  set(key: K, value?: V): WeakMap<K, V>;
  [Symbol.toStringTag]: string;
}

interface WeakMapConstructor {
  new <K, V>(): WeakMap<K, V>;
  new <K, V>(iterable: Iterable<[K, V]>): WeakMap<K, V>;
  prototype: WeakMap<any, any>;
}
declare var WeakMap: WeakMapConstructor;

interface Set<T> {
  add(value: T): Set<T>;
  clear(): void;
  delete(value: T): boolean;
  entries(): IterableIterator<[T, T]>;
  forEach(callbackfn: (value: T, index: T, set: Set<T>) => void, thisArg?: any): void;
  has(value: T): boolean;
  keys(): IterableIterator<T>;
  size: number;
  values(): IterableIterator<T>;
  [Symbol.iterator]():IterableIterator<T>;
  [Symbol.toStringTag]: string;
}

interface SetConstructor {
  new <T>(): Set<T>;
  new <T>(iterable: Iterable<T>): Set<T>;
  prototype: Set<any>;
}
declare var Set: SetConstructor;

interface WeakSet<T> {
  add(value: T): WeakSet<T>;
  clear(): void;
  delete(value: T): boolean;
  has(value: T): boolean;
  [Symbol.toStringTag]: string;
}

interface WeakSetConstructor {
  new <T>(): WeakSet<T>;
  new <T>(iterable: Iterable<T>): WeakSet<T>;
  prototype: WeakSet<any>;
}
declare var WeakSet: WeakSetConstructor;
