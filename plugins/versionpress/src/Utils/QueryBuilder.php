<?php
namespace VersionPress\Utils;

class QueryBuilder {

    /** @var string */
    private $query;

    /** @var array */
    private $queryObject;

    /**
     * @param string $query
     */
    function __construct($query) {
        $this->query = $query;
        $this->queryObject = self::parseQuery($query);
    }

    /**
     * @param string $query
     * @return array
     */
    public static function parseQuery($query) {
        // Regularize white spacing
        // Make in-between white spaces a unique space
        $query = preg_replace('/ {2,}/', ' ', trim($query));

        // https://regex101.com/r/wT6zG3/5
        $regex = "/(-)?(?:(\\S+):\\s*)?(?:'((?:[^'\\\\]|\\\\.)*)'|\"((?:[^\"\\\\]|\\\\.)*)\"|(\\S+))/";

        preg_match_all($regex, $query, $matches, PREG_SET_ORDER);

        $terms = array();

        foreach($matches as $match) {
            $key = empty($match[2]) ? 'text' : $match[2];
            if (!isset($terms[$key])) {
                $terms[$key] = array();
            }

            $terms[$key][] = array(
                'neg' => $match[1] === '-',
                'val' => isset($match[5]) ? $match[5] : (
                            isset($match[4]) ? $match[4] : (
                                isset($match[3]) ? $match[3] : ''))
            );
        }

        return $terms;
    }

    /**
     * @return string
     */
    public function getGitLogQuery() {
        $query = '-i --all-match';

        $q = $this->queryObject;

        if (isset($q['author'])) {
            foreach ($q['author'] as $value) {
                $query .= ' --author="' . $value['val'] . '"';
            }
        }

        if (isset($q['date'])) {
            foreach ($q['date'] as $value) {
                $val = preg_replace('/\s+/', '', $value['val']);

                $bounds = explode('..', $val);
                if (count($bounds) > 1) {
                    if ($bounds[0] !== '*') {
                        $query .= ' --after=' . date('Y-m-d', strtotime($bounds[0] . ' -1 day'));
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
        }

        if (isset($q['entity']) || isset($q['action']) || isset($q['vpid'])) {
            $entity = $action = $vpid = array();

            foreach (array('entity', 'action', 'vpid') as $item) {
                if (isset($q[$item])) {
                    foreach ($q[$item] as $value) {
                        ${$item}[] = $value['val'];
                    }
                }
            }

            if (!empty($entity) || !empty($action) || !empty($vpid)) {
                $query .= ' --grep="^VP-Action: ' .
                    (empty($entity) ? '.*' : '\(' . implode('\|', $entity) . '\)') . '/' .
                    (empty($action) ? '.*' : '\(' . implode('\|', $action) . '\)') . '/' .
                    (empty($vpid) ? '.*' : '\(' . implode('\|', $vpid) . '\)') .
                    '"';
            }
        }

        if (isset($q['text'])) {
            $patterns = array();
            foreach ($q['text'] as $value) {
                $patterns[] = $value['val'];
            }

            if (!empty($patterns)) {
                $query .= ' --grep="\(' . implode('\|', $patterns) . '\)"';
            }
        }

        foreach ($q as $key => $values) {
            if (in_array($key, array('author', 'date', 'entity', 'action', 'vpid', 'text'))) {
                continue;
            }

            $patterns = array();
            foreach ($values as $value) {
                $patterns[] = $value['val'];
            }

            if (!empty($patterns)) {
                $query .= ' --grep="^vp-' . $key . ': \(' . implode('\|', $patterns) . '\)"';
            }
        }

        return $query;
    }

}
