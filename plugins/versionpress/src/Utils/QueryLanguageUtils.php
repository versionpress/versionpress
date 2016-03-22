<?php

namespace VersionPress\Utils;

class QueryLanguageUtils {

    const VALUE_WILDCARD = 'VALUE_WILDCARD';
    const VALUE_STRING = 'VALUE_STRING';

    // https://regex101.com/r/wT6zG3/4 (query language)
    private static $queryRegex = "/(-)?(?:(\\S+):\\s*)?(?:'((?:[^'\\\\]|\\\\.)*)'|\"((?:[^\"\\\\]|\\\\.)*)\"|(\\S+))/";

    // https://regex101.com/r/pL2zA2/3 (support for * wildcard)
    private static $valueWildcardRegex = "/(?:\\\\\\\\)|(?:\\\\\\*)|(?:\\*)|(?:[^\\\\\\*]+)/";

    /**
     * Transforms queries into arrays for easier manipulation.
     * Example of transformation of one query (method works with list):
     *  query = some_field: value other_field: other_value
     *  array = ['some_field' => ['value'], 'other_field' => ['other_value']]
     *
     * @param $queries
     * @param $allowEmpty boolean Allow empty values
     * @return array
     */
    public static function createRulesFromQueries($queries, $allowEmpty = false) {
        $rules = array();
        foreach ($queries as $query) {
            preg_match_all(self::$queryRegex, $query, $matches, PREG_SET_ORDER);
            $isValidRule = count($matches) > 0;
            if (!$isValidRule) {
                continue;
            }

            $ruleParts = array();
            foreach($matches as $match) {
                $key = empty($match[2]) ? 'text' : strtolower($match[2]);

                /* value can be in 3rd, 4th or 5th index
                 *
                 * 3rd index => value is in single quotes
                 * 4th index => value is in double quotes
                 * 5th index => value is without quotes
                 */
                $value = strtolower(isset($match[5]) ? $match[5] : (
                                        isset($match[4]) ? $match[4] : (
                                            isset($match[3]) ? $match[3] : '')));

                if ($value !== '' || $allowEmpty) {
                    if (!isset($ruleParts[$key])) {
                        $ruleParts[$key] = array();
                    }
                    $ruleParts[$key][] = $value;
                }
            }

            $rules[] = $ruleParts;
        }
        return $rules;
    }

    /**
     * Tests if entity satisfies at least one of given rules.
     *
     * @param $entity
     * @param $rules
     * @return bool
     */
    public static function entityMatchesSomeRule($entity, $rules) {
        return ArrayUtils::any($rules, function ($rule) use ($entity) {
            return ArrayUtils::all($rule, function ($values, $field) use ($entity) { // check all parts of rule
                if (!isset($entity[$field])) {
                    return false;
                }

                $value = $values[0]; // Use single value per field
                $valueTokens = QueryLanguageUtils::tokenizeValue($value);
                $isWildcard = QueryLanguageUtils::tokensContainWildcard($valueTokens);

                if ($isWildcard && preg_match(QueryLanguageUtils::tokensToRegex($valueTokens), $entity[$field])) {
                    return true;
                } elseif ($entity[$field] == $value) {
                    return true;
                }

                return false;
            });
        });
    }

    /**
     * Converts rule (array) to query string to be used as an argument for `git log`.
     *
     * @param $rule array
     * @return string
     */
    public static function createGitLogQueryFromRule($rule) {
        $query = '-i --all-match';

        if (isset($rule['author'])) {
            foreach ($rule['author'] as $value) {
                $query .= ' --author="' . $value . '"';
            }
        }

        if (isset($rule['date'])) {
            foreach ($rule['date'] as $value) {
                $val = preg_replace('/\s+/', '', $value);

                $bounds = explode('..', $val);
                if (count($bounds) > 1) {
                    if ($bounds[0] !== '*') {
                        $query .= ' --after=' . date('Y-m-d', strtotime($bounds[0] . ' -1 day'));
                    }
                    if ($bounds[1] !== '*') {
                        $query .= ' --before=' . date('Y-m-d', strtotime($bounds[1] . ' +1 day'));
                    }
                    continue;
                }

                if (in_array(($op = substr($val, 0, 2)), array('<=', '>='))) {
                    $date = substr($val, 2);
                } else if (in_array(($op = substr($val, 0, 1)), array('<', '>'))) {
                    $date = substr($val, 1);
                } else {
                    $op = '';
                    $date = $val;
                };

                if ($op === '>=') {
                    $query .= ' --after=' . date('Y-m-d', strtotime($date . ' -1 day'));
                } else if ($op === '>') {
                    $query .= ' --after=' . date('Y-m-d', strtotime($date));
                } else if ($op === '<=') {
                    $query .= ' --before=' . date('Y-m-d', strtotime($date));
                } else if ($op === '<') {
                    $query .= ' --before=' . date('Y-m-d', strtotime($date . '-1 day'));
                } else {
                    $query .= ' --after=' . date('Y-m-d', strtotime($date . ' -1 day'));
                    $query .= ' --before=' . date('Y-m-d', strtotime($date));
                }
            }
        }

        if (!empty($rule['action'])) {
            $action = array_filter($rule['action'], function ($val) { return strpos($val, '/') === false; });
            $vpAction = array_filter($rule['action'], function ($val) { return strpos($val, '/') !== false; });
            if (!empty($vpAction)) {
                $query .= ' --grep="^VP-Action: \(' . implode('\|', $vpAction) . '\)"';
            }
        }

        if (!empty($rule['entity']) || !empty($action) || !empty($rule['vpid'])) {
            $query .= ' --grep="^VP-Action: ' .
                (empty($rule['entity']) ? '.*' : '\(' . implode('\|', $rule['entity']) . '\)') . '/' .
                (empty($action)         ? '.*' : '\(' . implode('\|', $action)         . '\)') . '/' .
                (empty($rule['vpid'])   ? '.*' : '\(' . implode('\|', $rule['vpid'])   . '\)') . '"';
        }

        if (!empty($rule['text'])) {
            $query .= ' --grep="\(' . implode('\|', $rule['text']) . '\)"';
        }

        foreach ($rule as $key => $values) {
            if (in_array($key, array('author', 'date', 'entity', 'action', 'vpid', 'text'))) {
                continue;
            }

            if (!empty($values)) {
                $query .= ' --grep="^vp-' . $key . ': \(' . implode('\|', $values) . '\)"';
            }
        }

        return $query;
    }

    /**
     * Converts rule (array) to isolated (enclosed in brackets) part of SQL restriction.
     *
     * Example:
     *  rule = ['field' => ['value'], 'other_field' => ['with_prefix*']]
     *  output = (`field` = "value" AND `other_field` LIKE "with_prefix%")
     *
     * @param $rule array
     * @return string
     */
    public static function createSqlRestrictionFromRule($rule) {
        $restrictionParts = array();

        foreach ($rule as $field => $values) {
            $value = $values[0]; // Use single value per field
            $valueTokens = self::tokenizeValue($value);
            $isWildcard = self::tokensContainWildcard($valueTokens);
            $searchedValue = self::tokensToSqlString($valueTokens);

            $operator = $isWildcard ? 'LIKE' : '=';

            $escapedValue = str_replace('"', '\"', $searchedValue);

            if ($isWildcard) {
                $escapedValue = str_replace('_', '\_', $escapedValue);
            }

            $restrictionPart = sprintf('`%s` %s "%s"', $field, $operator, $escapedValue);
            $restrictionParts[] = $restrictionPart;
        }

        return sprintf('(%s)', join(' AND ', $restrictionParts));
    }

    /**
     * Splits value into tokens.
     * Tokens:
     *   *    => VALUE_WILDCARD,
     *   else => VALUE_STRING
     *
     * @param $value
     * @return array
     */
    private static function tokenizeValue($value) {
        preg_match_all(self::$valueWildcardRegex, $value, $matches);
        $tokens = array();

        foreach ($matches[0] as $valuePart) {
            if ($valuePart === '*') {
                $tokens[] = array(
                    'type' => self::VALUE_WILDCARD
                );
            } else if ($valuePart === '\*') {
                $tokens[] = array(
                    'type' => self::VALUE_STRING,
                    'value' => '*',
                );
            } else if ($valuePart === '\\\\') {
                $tokens[] = array(
                    'type' => self::VALUE_STRING,
                    'value' => '\\',
                );
            } else {
                $tokens[] = array(
                    'type' => self::VALUE_STRING,
                    'value' => $valuePart,
                );
            }
        }

        return $tokens;
    }

    /**
     * Converts tokens to regular expression.
     * Wildcard is replaced by '.*'.
     *
     * For tokens from value 'prefix*' it returns '/^prefix.*$/'.
     *
     * @param $valueTokens
     * @return string
     */
    private static function tokensToRegex($valueTokens) {
        $regexDelimiter = '/';
        $regexFromValue = join('', array_map(function ($token) use ($regexDelimiter) {
            return QueryLanguageUtils::tokenToRegex($token, $regexDelimiter);
        }, $valueTokens));

        return sprintf('%s^%s$%s', $regexDelimiter, $regexFromValue, $regexDelimiter);
    }

    private static function tokenToRegex($token, $delimiter) {
        return $token['type'] === self::VALUE_WILDCARD ? '.*' : preg_quote($token['value'], $delimiter);
    }

    private static function tokensToSqlString($valueTokens) {
        return join('', array_map(array('VersionPress\Utils\QueryLanguageUtils', 'tokenToSqlString'), $valueTokens));
    }

    private static function tokenToSqlString($token) {
        return $token['type'] === self::VALUE_WILDCARD ? '%' : $token['value'];
    }

    private static function tokensContainWildcard($valueTokens) {
        return array_search(self::VALUE_WILDCARD, array_column($valueTokens, 'type')) !== false;
    }
}
