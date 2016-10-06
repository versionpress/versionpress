export const IN_QUOTES_INCOMPLETE_REGEXP = /^(".*)|('.*)$/;
export const IN_QUOTES_REGEXP = /^(".*")|('.*')$/;

export function trim(value: string, allowIncomplete: boolean = false) {
  value = value.trim();
  if (IN_QUOTES_REGEXP.test(value)) {
    value = value.slice(1, -1);
  }
  if (allowIncomplete && IN_QUOTES_INCOMPLETE_REGEXP.test(value)) {
    value = value.slice(1);
  }

  return value;
}
