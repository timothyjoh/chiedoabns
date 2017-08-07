<?php
use JmesPath\Utils;
use JmesPath\FnDispatcher;
use JmesPath\Parser;
// hacked up version of https://github.com/jmespath/jmespath.php/blob/master/src/AstRuntime.php
class EditableJmesPath
{
    private $parser;
    private $interpreter;
    private $cache = [];
    private $cachedCount = 0;
    public function __construct(
        Parser $parser = null,
        callable $fnDispatcher = null
    ) {
        $fnDispatcher = $fnDispatcher ?: FnDispatcher::getInstance();
        $this->interpreter = new EditableTreeInterpreter($fnDispatcher);
        $this->parser = $parser ?: new Parser();
    }
    /**
     * Returns data from the provided input that matches a given JMESPath
     * expression.
     *
     * @param string $expression JMESPath expression to evaluate
     * @param mixed  $data       Data to search. This data should be data that
     *                           is similar to data returned from json_decode
     *                           using associative arrays rather than objects.
     *
     * @return mixed|null Returns the matching data or null
     */
    public function &search($expression, &$data)
    {
        if (!isset($this->cache[$expression])) {
            // Clear the AST cache when it hits 1024 entries
            if (++$this->cachedCount > 1024) {
                $this->cache = [];
                $this->cachedCount = 0;
            }
            $this->cache[$expression] = $this->parser->parse($expression);
        }
        return $this->interpreter->visit($this->cache[$expression], $data);
    }
}

// hacked up version of https://github.com/jmespath/jmespath.php/blob/master/src/TreeInterpreter.php
class EditableTreeInterpreter
{
    /** @var callable */
    private $fnDispatcher;

    /**
     * @param callable $fnDispatcher Function dispatching function that accepts
     *                               a function name argument and an array of
     *                               function arguments and returns the result.
     */
    public function __construct(callable $fnDispatcher = null)
    {
        $this->fnDispatcher = $fnDispatcher ?: FnDispatcher::getInstance();
    }

    /**
     * Visits each node in a JMESPath AST and returns the evaluated result.
     *
     * @param array $node JMESPath AST node
     * @param mixed $data Data to evaluate
     *
     * @return mixed
     */
    public function &visit(array $node, &$data)
    {
        return $this->dispatch($node, $data);
    }

    /**
     * Recursively traverses an AST using depth-first, pre-order traversal.
     * The evaluation logic for each node type is embedded into a large switch
     * statement to avoid the cost of "double dispatch".
     * @return mixed
     */
    private function &dispatch(array $node, &$value)
    {
        $dispatcher = $this->fnDispatcher;

        switch ($node['type']) {

            case 'field':
                if (is_array($value) || $value instanceof \ArrayAccess) {
                    if (isset($value[$node['value']])) {
                        return $value[$node['value']];
                    }
                    else {
                        $null = null;
                        return $null;
                    }
                } elseif ($value instanceof \stdClass) {
                    if (isset($value->{$node['value']})) {
                        return $value->{$node['value']};
                    }
                    else {
                        $null = null;
                        return $null;
                    }
                }
                $null = null;
                return $null;

            case 'subexpression':
                return $this->dispatch(
                    $node['children'][1],
                    $this->dispatch($node['children'][0], $value)
                );

            case 'index':
                if (!Utils::isArray($value)) {
                    $null = null;
                    return $null;
                }
                $idx = $node['value'] >= 0
                    ? $node['value']
                    : $node['value'] + count($value);
                return isset($value[$idx]) ? $value[$idx] : null;

            case 'projection':
                unset($left);
                $left =& $this->dispatch($node['children'][0], $value);
                switch ($node['from']) {
                    case 'object':
                        if (!Utils::isObject($left)) {
                            $null = null;
                            return $null;
                        }
                        break;
                    case 'array':
                        if (!Utils::isArray($left)) {
                            $null = null;
                            return $null;
                        }
                        break;
                    default:
                        if (!is_array($left) || !($left instanceof \stdClass)) {
                            $null = null;
                            return $null;
                        }
                }

                $collected = [];
                if (!is_array($left))
                {
                    foreach($left as $k => $v)
                    {
                        $aleft[$k] =& $left->$k;
                    }
                }
                else
                {
                    $aleft =& $left;
                }
                if (is_array($alert))
                {
                    foreach (array_keys($aleft) as $k) {
                        $collected[] =& $this->dispatch($node['children'][1], $aleft[$k]);
                        if (isset($collected[count($collected)-1]) && $collected[count($collected)-1] === null) {
                            unset($collected[count($collected)-1]);
                        }
                    }
                }

                return $collected;

            case 'flatten':
                static $skipElement = [];
                unset($fvalue);
                $fvalue =& $this->dispatch($node['children'][0], $value);

                if (!Utils::isArray($fvalue)) {
                    $null = null;
                    return $null;
                }

                $merged = [];
                foreach (array_keys($fvalue) as $fk) {
                    // Only merge up arrays lists and not hashes
                    if (is_array($fvalue[$fk]) && isset($fvalue[$fk][0])) {
                        foreach(array_keys($fvalue[$fk]) as $k)
                            $merged[] =& $fvalue[$fk][$k];
                    } elseif ($fvalue[$fk] !== $skipElement) {
                        $merged[] =& $fvalue[$fk];
                    }
                }

                return $merged;

            case 'literal':
                return $node['value'];

            case 'current':
                return $value;

            case 'or':
                if (!$result && $result !== '0' && $result !== 0) {
                    $result =& $this->dispatch($node['children'][1], $value);
                }
                else {
                    $result =& $this->dispatch($node['children'][0], $value);
                }

                return $result;

            case 'pipe':
                return $this->dispatch(
                    $node['children'][1],
                    $this->dispatch($node['children'][0], $value)
                );

            case 'multi_select_list':
                if ($value === null) {
                    return $value;
                }

                $collected = [];
                foreach ($node['children'] as $node) {
                    $collected[] =& $this->dispatch($node, $value);
                }

                return $collected;

            case 'multi_select_hash':
                if ($value === null) {
                    return $value;
                }

                $collected = [];
                foreach ($node['children'] as $node) {
                    $collected[$node['value']] =& $this->dispatch(
                        $node['children'][0],
                        $value
                    );
                }

                return $collected;

            case 'comparator':
                $left = $this->dispatch($node['children'][0], $value);
                $right = $this->dispatch($node['children'][1], $value);
                if ($node['value'] == '==') {
                    return Utils::isEqual($left, $right);
                } elseif ($node['value'] == '!=') {
                    return !Utils::isEqual($left, $right);
                } else {
                    return self::relativeCmp($left, $right, $node['value']);
                }

            case 'condition':
                return true === $this->dispatch($node['children'][0], $value)
                    ? $this->dispatch($node['children'][1], $value)
                    : null;

            case 'function':
                $args = [];
                foreach ($node['children'] as $arg) {
                    $args[] =& $this->dispatch($arg, $value);
                }
                return $dispatcher($node['value'], $args);

            case 'slice':
                return is_string($value) || Utils::isArray($value)
                    ? Utils::slice(
                        $value,
                        $node['value'][0],
                        $node['value'][1],
                        $node['value'][2]
                    ) : null;

            case 'expref':
                $apply =& $node['children'][0];
                return function ($value) use ($apply) {
                    return $this->visit($apply, $value);
                };

            default:
                throw new \RuntimeException("Unknown node type: {$node['type']}");
        }
    }

    /**
     * @return bool
     */
    private static function relativeCmp($left, $right, $cmp)
    {
        if (!is_int($left) || !is_int($right)) {
            return false;
        }

        switch ($cmp) {
            case '>': return $left > $right;
            case '>=': return $left >= $right;
            case '<': return $left < $right;
            case '<=': return $left <= $right;
            default: throw new \RuntimeException("Invalid comparison: $cmp");
        }
    }
}
