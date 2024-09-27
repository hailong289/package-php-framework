<?php
namespace Hola\Database;
class QueryBuilder {

    private Connection $connection;
    private $model = null;

    public function __construct() {
        $this->connection = new Connection();
    }

    /** @var array[]  */
    public $bindings = [
        'select' => [],
        'from' => [],
        'join' => [],
        'where' => [],
        'groupBy' => [],
        'having' => [],
        'order' => [],
        'union' => [],
        'limit' => [],
        'offset' => [],
        'insertOrUpdate' => [],
        'delete' => [],
        'relations' => []
    ];

    public function setModel($nameModel)
    {
        $this->model = new $nameModel();
    }

    public function connection($conn = null)
    {
        $this->connection = new Connection($conn);
        return $this;
    }

    public function beginTransaction()
    {
        $this->connection->beginTransaction();
        return $this;
    }

    public function commit()
    {
        $this->connection->commit();
        return $this;
    }

    public function rollBack()
    {
        $this->connection->rollBack();
        return $this;
    }

    public function select($columns = ['*'])
    {
        $this->bindings['select'] = is_array($columns) ? $columns : func_get_args();
        return $this;
    }

    public function from($table, $as = null)
    {
        $this->bindings['from'] = compact('table', 'as');
        return $this;
    }

    public function join($table, $first, $operator = null, $second = null, $type = 'INNER')
    {
        $this->bindings['join'][] = compact('table', 'first', 'operator', 'second', 'type');
        return $this;
    }

    public function leftJoin($table, $first, $operator = null, $second = null)
    {
        return $this->join($table, $first, $operator, $second, 'LEFT');
    }

    public function rightJoin($table, $first, $operator = null, $second = null)
    {
        return $this->join($table, $first, $operator, $second, 'RIGHT');
    }

    public function crossJoin($table, $first, $operator = null, $second = null)
    {
        return $this->join($table, $first, $operator, $second, 'CROSS');
    }

    public function where($column, $operator = null, $value = null)
    {
        if ($column instanceof \Closure) {
            $builder = clone $this;
            $this->bindings['where'][] = [
                'type' => 'nested',
                'query' => $column($builder)->bindings['where']
            ];
            return $this;
        }
        if (func_num_args() === 2) {
            list($value, $operator) = [$operator, '='];
        }
        $boolean = !empty($this->bindings['where']) ? ' AND ' : '';
        $this->bindings['where'][] = compact('column', 'operator', 'value', 'boolean');
        return $this;
    }

    public function orWhere($column, $operator = null, $value = null)
    {
        if ($column instanceof \Closure) {
            $builder = clone $this;
            $this->bindings['where'][] = [
                'type' => 'nested',
                'query' => $column($builder)->bindings['where']
            ];
            return $this;
        }
        if (func_num_args() === 2) {
            list($value, $operator) = [$operator, '='];
        }
        $boolean = !empty($this->bindings['where']) ? ' OR ' : '';
        $this->bindings['where'][] = compact('column', 'operator', 'value', 'boolean');
        return $this;
    }

    public function whereLike($column, $value)
    {
        $boolean = !empty($this->bindings['where']) ? ' AND ' : '';
        $operator = " LIKE ";
        $this->bindings['where'][] = compact('column', 'operator', 'value', 'boolean');
        return $this;
    }

    public function orWhereLike($column, $value)
    {
        $boolean = !empty($this->bindings['where']) ? ' OR ' : '';
        $operator = " LIKE ";
        $this->bindings['where'][] = compact('column', 'operator', 'value', 'boolean');
        return $this;
    }

    public function whereBetween($column, array $value)
    {
        list($value1, $value2) = $value;
        $boolean = !empty($this->bindings['where']) ? ' AND ' : '';
        $operator = " BETWEEN ";
        $value = "$value1 AND $value2";
        $this->bindings['where'][] = compact('column', 'operator', 'value', 'boolean');
        return $this;
    }

    public function whereIn($column, array $value)
    {
        $boolean = !empty($this->bindings['where']) ? ' AND ' : '';
        $operator = " IN ";
        $value = "(".implode(', ', $value).")";
        $this->bindings['where'][] = compact('column', 'operator', 'value', 'boolean');
        return $this;
    }

    public function whereNotIn($column, array $value)
    {
        $boolean = !empty($this->bindings['where']) ? ' AND ' : '';
        $operator = " NOT IN ";
        $value = "(".implode(', ', $value).")";
        $this->bindings['where'][] = compact('column', 'operator', 'value', 'boolean');
        return $this;
    }

    public function whereRaw($sql)
    {
        $boolean = !empty($this->bindings['where']) ? ' AND ' : '';
        $this->bindings['where'][] = [
            'type' => 'raw',
            'sql' => $sql,
            'boolean' => $boolean
        ];
        return $this;
    }

    public function orWhereRaw($sql)
    {
        $boolean = !empty($this->bindings['where']) ? ' OR ' : '';
        $this->bindings['where'][] = [
            'type' => 'raw',
            'sql' => $sql,
            'boolean' => $boolean
        ];
        return $this;
    }

    public function groupBy($columns)
    {
        $this->bindings['groupBy'] = is_array($columns) ? $columns : func_get_args();
        return $this;
    }

    public function having($column, $operator = null, $value = null, $boolean = 'and')
    {
        $this->bindings['having'][] = compact('column', 'operator', 'value', 'boolean');
        return $this;
    }

    public function orderBy($column, $direction = 'asc')
    {
        $this->bindings['order'][] = compact('column', 'direction');
        return $this;
    }

    public function limit($value)
    {
        $this->bindings['limit'] = $value;
        return $this;
    }

    public function offset()
    {
        $this->bindings['offset'] = $value;
        return $this;
    }

    public function union($query, $all = false)
    {
        $this->bindings['union'][] = compact('query', 'all');
        return $this;
    }

    public function relations($model, $model_many = null, $name, $foreign_key, $foreign_key2 = null, $primary_key, $relation)
    {
        $log_debug = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];
        return [
            'model' => $model,
            'model_many' => $model_many,
            'name' => $name,
            'foreign_key' => $foreign_key,
            'foreign_key2' => $foreign_key2,
            'primary_key' => $primary_key,
            'relation' => $relation,
            'log' => $log_debug
        ];
    }

    public function with($name, $useN1Query = false, $closure = null)
    {
        if (is_array($name)) {
            foreach ($name as $key => $relation) {
                if (is_numeric($key)) {
                    $this->with($relation, $useN1Query);
                    continue;
                } else {
                    $this->with($key, $useN1Query, $relation);
                }
            }
            return $this;
        } elseif ($closure instanceof \Closure) {
            $builder = clone $this;
            $query = $closure($builder)->toSql();
            if (!is_null($this->model) && method_exists($this->model, $name)) {
                $this->bindings['relations'][] = array_merge($this->model->{$name}(), [
                    'useN1Query' => $useN1Query,
                    'query' => $query
                ]);
            }
            return $this;
        }
        list($name, $select) = array_pad(explode(':', $name), 2, null);
        if (!is_null($this->model) && method_exists($this->model, $name)) {
            $this->bindings['relations'][] = array_merge($this->model->{$name}(), [
                'useN1Query' => $useN1Query,
                'query' => empty($select) ?  null : "SELECT {$select} FROM {$name}"
            ]);
        }
        return $this;
    }

    public function toSql($type = 'SELECT') {
        switch ($type) {
            case 'SELECT':
                $sql = $this->resloveSelect();
                $this->resloveTable($sql);
                break;
            case 'INSERT':
                $sql = $this->resloveInsert();
            case 'UPDATE':
                $sql = $this->resloveUpdate();
                break;
            default:
                $sql = $this->resloveSelect();
                $this->resloveTable($sql);
                break;
        }
        $this->resloveJoin($sql);
        $this->resloveWhere($sql);
        $this->resloveGroupBy($sql);
        $this->resloveHaving($sql);
        $this->resloveOrderBy($sql);
        $this->resloveLimit($sql);
        $this->resloveOffset($sql);
        $this->resloveUnion($sql);
        $this->clearBindings();
        return $sql;
    }

    public function get()
    {
        return collection($this->connection->select($this->toSql()))
            ->map(fn ($item) => $this->resloveAttribute($item, 'GET'));
    }

    public function first()
    {
        return collection($this->connection->selectOne($this->toSql()))
            ->mapFirst(fn ($item) => $this->resloveAttribute($item, 'GET'));
    }

    public function insert($data)
    {
        $this->resloveAttribute($data);
        $this->bindings['insertOrUpdate'] = $data;
        return $this->connection->insert($this->toSql('INSERT'));
    }

    public function insertLastId($data)
    {
        $this->resloveAttribute($data);
        $this->bindings['insertOrUpdate'] = $data;
        return $this->connection->insertLastId($this->toSql('INSERT'));
    }

    public function update($data, $id = null)
    {
        if ($id) {
            $this->where('id', $id);
        }
        $this->resloveAttribute($data);
        $this->bindings['insertOrUpdate'] = $data;
        return $this->connection->insert($this->toSql('UPDATE'));
    }

    public function delete($data, $id = null)
    {
        if ($id) {
            $this->where('id', $id);
        }
        $this->bindings['delete'] = $data;
        return $this->connection->insert($this->toSql('DELETE'));
    }

    private function resloveSelect()
    {
        return 'SELECT ' . implode(', ', $this->bindings['select']);
    }

    private function resloveInsert()
    {
        foreach ($this->bindings['insertOrUpdate'] as $key => $value) {
            $columns[] = $key;
            $values[] = $value;
        }
        return 'INSERT INTO ' . $this->bindings['from']['table'] . ' (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $values) . ')';
    }

    private function resloveUpdate()
    {
        return 'UPDATE ' . $this->bindings['from']['table'] . ' SET ' . implode(', ', $this->bindings['insertOrUpdate']);
    }

    private function resloveTable(&$sql)
    {
        if (empty($this->bindings['from'])) {
            return '';
        }
        $sql = ' FROM ' . $this->bindings['from']['table'] . ($this->bindings['from']['as'] ? ' AS ' . $this->bindings['from']['as'] : '');
        return $sql;
    }

    private function resloveJoin(&$sql)
    {
        if (empty($this->bindings['join'])) {
            return '';
        }
        foreach ($this->bindings['join'] as $join) {
            $sql .= " {$join['type']} JOIN {$join['table']} ON {$join['first']} {$join['operator']} {$join['second']}";
        }
        return $sql;
    }

    private function resloveWhere(&$sql, $bindings = [])
    {
        $bindings = empty($bindings) ? $this->bindings['where'] : $bindings;
        if (empty($this->bindings['where'])) {
            return '';
        }
        $sql .= ' WHERE ';
        foreach ($this->bindings['where'] as $where) {
            if ($where['type'] === 'nested') {
                $sql .= '(' . $this->resloveWhere($sql, $where['query']) . ')';
            } elseif ($where['type'] === 'raw') {
                $sql .= "{$where['boolean']}{$where['sql']}";
            } else {
                $sql .= "{$where['boolean']}{$where['column']} {$where['operator']} {$where['value']}";
            }
        }
        return $sql;
    }

    private function resloveUnion(&$sql)
    {
        if (empty($this->bindings['union'])) {
            return '';
        }
        foreach ($this->bindings['union'] as $union) {
            $sql .= " UNION " . ($union['all'] ? 'ALL ' : '') . $union['query']->toSql();
        }
        return $sql;
    }

    private function resloveGroupBy(&$sql)
    {
        if (empty($this->bindings['groupBy'])) {
            return '';
        }
        $sql = ' GROUP BY ' . implode(', ', $this->bindings['groupBy']);
        return $sql;
    }

    private function resloveHaving(&$sql)
    {
        if (empty($this->bindings['having'])) {
            return '';
        }
        foreach ($this->bindings['having'] as $having) {
            $sql .= " HAVING {$having['column']} {$having['operator']} {$having['value']} {$having['boolean']}";
        }
        return $sql;
    }

    private function resloveOrderBy(&$sql)
    {
        if (empty($this->bindings['order'])) {
            return '';
        }
        foreach ($this->bindings['order'] as $order) {
            $sql .= " ORDER BY {$order['column']} {$order['direction']}";
        }
        return $sql;
    }

    private function resloveLimit(&$sql)
    {
        if (empty($this->bindings['limit'])) {
            return '';
        }
        $sql .= " LIMIT {$this->bindings['limit']}";
        return $sql;
    }

    private function resloveOffset(&$sql)
    {
        if (empty($this->bindings['offset'])) {
            return '';
        }
        $sql .= " OFFSET {$this->bindings['offset']}";
        return $sql;
    }

    private function resloveAttribute(&$data, $type = 'SET')
    {
        $attribute = $type === 'SET' ? 'setAttributes' : 'getAttributes';
        foreach ($data as $key => $value) {
            $method = $attribute.ucfirst($key);
            if (!is_null($this->model) && method_exists($this->model, $method)) {
                $data[$key] = $this->model->{$method}($value);
            }
        }
        return $sql;
    }

    private function clearBindings()
    {
        foreach ($this->bindings as $key => $value) {
           if(!empty($this->bindings[$key]))  $this->bindings[$key] = [];
        }
    }

}