<?php
namespace Hola\Database;
use Hola\Data\Collection;

class QueryBuilder {

    public static Connection|null $connection = null;
    private $model = null;

    public function __construct() {}

    private static function connect()
    {
        if (is_null(self::$connection)) {
            self::$connection = new Connection();
        }
        return self::$connection;
    }

    public static function conn()
    {
        self::connect();
        return new self();
    }

    /** @var array[]  */
    public $bindings = [
        'select' => [],
        'function' => [
            'count' => [],
            'sum' => [],
            'avg' => [],
            'min' => [],
            'max' => [],
            'distinct' => []
        ],
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
        'relations' => [],
        'variables' => [],
        'params' => []
    ];

    public function setModel($nameModel, $varModel = [])
    {
        $this->model = new $nameModel();
        $this->bindings['variables'] = $varModel;
    }

    public function connection($conn = null, $type = null)
    {
        self::$connection = new Connection($conn, $type = null);
        return $this;
    }

    public function reconnectDefault()
    {
        self::$connection = new Connection();
        return $this;
    }

    public function enableQueryLog()
    {
        return self::connect()->enableQueryLog();
    }

    public function getQueryLog()
    {
        return self::connect()->getQueryLog();
    }

    public function beginTransaction()
    {
        return self::connect()->beginTransaction();
    }

    public function commit()
    {
        return self::connect()->commit();;
    }

    public function rollBack()
    {
        return self::connect()->rollBack();
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
        $boolean = !empty($this->bindings['where']) ? ' AND ' : '';
        if ($column instanceof \Closure) {
            $builder = clone $this;
            $builder->clearBindings(true);
            $this->bindings['where'][] = [
                'type' => 'nested',
                'boolean' => $boolean,
                'query' => $column($builder)->bindings['where']
            ];
            return $this;
        } elseif (is_array($column)) {
            foreach ($column as $key => $value) {
                $this->where($key, '=', $value);
            }
            return $this;
        }
        if (is_null($value)) {
            list($value, $operator) = [$operator, '='];
        }
        $this->bindings['where'][] = compact('column', 'operator', 'value', 'boolean');
        return $this;
    }

    public function orWhere($column, $operator = null, $value = null)
    {
        $boolean = !empty($this->bindings['where']) ? ' OR ' : '';
        if ($column instanceof \Closure) {
            $builder = clone $this;
            $builder->clearBindings(true);
            $this->bindings['where'][] = [
                'type' => 'nested',
                'boolean' => $boolean,
                'query' => $column($builder)->bindings['where']
            ];
            return $this;
        } elseif (is_array($column)) {
            foreach ($column as $key => $value) {
                $this->where($key, '=', $value);
            }
            return $this;
        }
        if (is_null($value)) {
            list($value, $operator) = [$operator, '='];
        }
        $this->bindings['where'][] = compact('column', 'operator', 'value', 'boolean');
        return $this;
    }

    public function whereLike($column, $value)
    {
        $boolean = !empty($this->bindings['where']) ? ' AND ' : '';
        $operator = " LIKE";
        $this->bindings['where'][] = compact('column', 'operator', 'value', 'boolean');
        return $this;
    }

    public function orWhereLike($column, $value)
    {
        $boolean = !empty($this->bindings['where']) ? ' OR ' : '';
        $operator = " LIKE";
        $this->bindings['where'][] = compact('column', 'operator', 'value', 'boolean');
        return $this;
    }

    public function whereBetween($column, array $value)
    {
        list($value1, $value2) = $value;
        $boolean = !empty($this->bindings['where']) ? ' AND ' : '';
        $operator = " BETWEEN";
        $type = 'between';
        $this->bindings['where'][] = compact('column', 'operator', 'value', 'boolean', 'type');
        return $this;
    }

    public function whereIn($column, array $value)
    {
        $boolean = !empty($this->bindings['where']) ? ' AND ' : '';
        $operator = " IN";
        $type = 'array';
        $this->bindings['where'][] = compact('column', 'operator', 'value', 'boolean', 'type');
        return $this;
    }

    public function whereNotIn($column, array $value)
    {
        $boolean = !empty($this->bindings['where']) ? ' AND ' : '';
        $operator = " NOT IN";
        $type = 'array';
        $this->bindings['where'][] = compact('column', 'operator', 'value', 'boolean', 'type');
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

    public function when($value, $callback)
    {
        if ($value) {
            $callback($this);
        }
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
        if (is_array($column)) {
            foreach ($column as $key => $value) {
                $this->orderBy($key, $value);
            }
            return $this;
        }
        $this->bindings['order'][] = compact('column', 'direction');
        return $this;
    }

    public function limit($value)
    {
        $this->bindings['limit'] = $value;
        return $this;
    }

    public function offset($value)
    {
        $this->bindings['offset'] = $value;
        return $this;
    }

    public function union($query, $all = false)
    {
        $this->bindings['union'][] = compact('query', 'all');
        return $this;
    }

    public function relations($related, $table_3rd, $name, $foreign_key, $foreign_key2, $current_key, $relation)
    {
        $log_debug = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];
        return [
            'related' => $related,
            'table_3rd' => $table_3rd,
            'name' => $name,
            'current_key' => [
                'name' => $current_key,
                'value' => null
            ],
            'foreign_key' => [
                'name' => $foreign_key,
                'value' => null
            ],
            'foreign_key2' => [
                'name' => $foreign_key2,
                'value' => null
            ],
            'relation' => $relation,
            'useN1Query' => false,
            'query' => null,
            'columns' => null,
            'log' => $log_debug
        ];
    }

    public function with($name, $useN1Query = false)
    {
        if (is_array($name)) {
            foreach ($name as $key => $relation) {
                if (is_numeric($key)) {
                    $this->with($relation, $useN1Query);
                    continue;
                } else {
                    $this->withClosure($key, $useN1Query, $relation);
                }
            }
            return $this;
        }
        list($name, $select) = array_pad(explode(':', $name), 2, null);
        if (!is_null($this->model) && method_exists($this->model, $name)) {
            $this->bindings['relations'][] = array_merge($this->model->{$name}(), [
                'useN1Query' => $useN1Query,
                'query' => null,
                'columns' => $select
            ]);
        }
        return $this;
    }

    private function withClosure($name, $useN1Query, \Closure $closure) {
        if (!is_null($this->model) && method_exists($this->model, $name)) {
            $this->bindings['relations'][] = array_merge($this->model->{$name}(), [
                'useN1Query' => $useN1Query,
                'query' => $closure,
                'columns' => null
            ]);
        }
        return $this;
    }

    public function toSql($type = 'SELECT', $bindings = []) {
        switch ($type) {
            case 'SELECT':
                $sql = $this->resloveSelect();
                $this->resloveTable($sql);
                break;
            case 'INSERT':
                $sql = $this->resloveInsert($bindings);
                break;
            case 'UPDATE':
                $sql = $this->resloveUpdate($bindings);
                break;
            case 'DELETE':
                $sql = $this->resloveDelete();
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

    public function subQuery($query, $as)
    {
        $this->bindings['from'] = [
            'table' => "($query)",
            'as' => $as
        ];
        return $this;
    }

    public function clone() {
        $clone = clone $this;
        return $clone->toSql();
    }

    public function dump() {
        $sql = $this->toSql();
        foreach ($this->bindings['params'] as $key => $value) {
            if (is_array($value)) {
               foreach ($value as $val) {
                   $sql = preg_replace('/\?/', $val, $sql);
               }
            } else {
                $sql = preg_replace('/\?/', $value, $sql);
            }
        }
        logs()->dump($sql);
        return $this;
    }

    public function get(): Collection|null
    {
        return $this->resloveData($this->toSql(), 'select', function ($selectData) {
            $this->clearBindings(true);
            return $selectData;
        }, $this->bindings['params']);
    }

    public function find($id): Collection|null
    {
        return $this->where('id', $id)->first();
    }

    public function first(): Collection|null
    {
        return $this->resloveData($this->toSql(), 'selectOne', function ($selectData) {
            $this->clearBindings(true);
            return $selectData;
        }, $this->bindings['params']);
    }

    public function create($data): Collection|null
    {
        $table = $this->bindings['from']['table'];
        return $this->resloveData($this->toSql('INSERT', $data), 'insertLastId', function ($id) use ($table) {
            $this->clearBindings(true);
            $selectData = $this->from($table)->find($id);
            return $selectData;
        }, $this->bindings['params']);
    }
    
    public function updateOrInsert($data, $id = null)
    {
        $table = $this->bindings['from']['table'];
        $selectData = $this->from($table)->find($id);
        if ($selectData->isEmpty()) {
            return $this->create($data);
        }
        return $this->update($data, $id);
    }
    
    public function insert($data)
    {
        return $this->resloveData($this->toSql('INSERT', $data), 'insert', function ($selectData, $status) {
            $this->clearBindings(true);
            return $status;
        }, $this->bindings['params']);
    }

    public function insertLastId($data)
    {
        return $this->resloveData($this->toSql('INSERT', $data), 'insertLastId', function ($id) {
            $this->clearBindings(true);
            return $id;
        }, $this->bindings['params']);
    }

    public function update($data, $id = null)
    {
        if (!is_null($id)) {
            $this->where('id', $id);
        }
        return $this->resloveData($this->toSql('UPDATE', $data), 'update', function ($selectData, $status) {
            $this->clearBindings(true);
            return $status;
        },$this->bindings['params']);
    }

    public function delete($id = null)
    {
        if (!is_null($id)) {
            $this->where('id', $id);
        }
        return $this->resloveData($this->toSql('DELETE'), 'delete', function ($selectData, $status) {
            $this->clearBindings(true);
            return $status;
        }, $this->bindings['params']);
    }

    public function softDelete($id = null)
    {
        $data = $this->resloveSoftDelete();
        return $this->update($data, $id);
    }

    public function query($sql)
    {
        return self::$connection->query($sql);
    }

    public function count($name = null, $alias = null){
        $this->bindings['function']['count'] = [
            'name' => $name ?? '*',
            'alias' => $alias ?? 'count'
        ];
        return $this->first();
    }

    public function sum($name, $alias){
        $this->bindings['function']['sum'] = [
            'name' => $name,
            'alias' => $alias
        ];
        return $this->first();
    }

    public function pagination($limit, $page = 1){
        $page = $page === 0 ? 1 : $page;
        $offset = ($page - 1) * $limit;
        $this->offset($offset)->limit($limit);
        return $this->get();
    }

    public function paginationWithCount($limit = 10, $page = 1)
    {
        $builder = clone $this;
        $page = $page === 0 ? 1 : $page;
        $offset = ($page - 1) * $limit;
        $total = $builder->count('*','total')->value('total');
        $data =  $this->offset($offset)->limit($limit)->get();
        $last_page = ceil($total / $limit);
        return collection([
            'items' => $data?->values(),
            'total' => $total,
            'limit' => $limit,
            'page' => $page,
            'last_page' => $last_page,
            'next_page' => $page < $last_page ? $page + 1 : null,
            'prev_page' => $page > 1 ? $page - 1 : null
        ])->values();
    }

    private function resloveData($sql, $select, $callback, $bindings = [])
    {
        $status = false;
        $selectData = [];
        switch ($select) {
            case 'select':
                $select = collection(self::$connection->select($sql, $bindings));
                if ($select->isEmpty()) {
                    return $callback(null, $status);
                }
                $selectData = $this->resloveRelations($select);
                $selectData = $selectData->map(fn ($item) => $this->resloveAttribute($item, 'GET'));
                break;
            case 'selectOne':
                $select = collection(self::$connection->selectOne($sql, $bindings));
                if ($select->isEmpty()) {
                    return $callback(null, $status);
                }
                $selectData = $this->resloveRelations($select, 'FIRST');
                $selectData = $selectData->mapFirst(fn ($item) => $this->resloveAttribute($item, 'GET'));
                break;
            case 'insert':
                $this->resloveAttribute($bindings);
                $status = self::$connection->insert($sql, $bindings);
                break;
            case 'insertLastId':
                $this->resloveAttribute($bindings);
                $selectData = self::$connection->insertLastId($sql, $bindings);
                break;
            case 'update':
                $this->resloveAttribute($bindings);
                $status = self::$connection->update($sql, $bindings);
                break;
            case 'delete':
                $status = self::$connection->delete($sql, $bindings);
                break;
        }
        return $callback($selectData, $status);
    }

    private function resloveSelect()
    {
        if (!empty($this->bindings['function']['count'])) {
            return 'SELECT '.$this->resloveFunction('count');
        }
        if (!empty($this->bindings['function']['sum'])) {
            return 'SELECT '.$this->resloveFunction('sum');
        }
        if (empty($this->bindings['select'])) {
            return 'SELECT *';
        }
        return 'SELECT ' . implode(', ', $this->bindings['select']);
    }

    private function resloveFunction($function)
    {
        $name = $this->bindings['function'][$function]['name'];
        $alias = $this->bindings['function'][$function]['alias'];
        return "$function($name) AS $alias";
    }

    private function resloveInsert($bindings = [])
    {
        $sql_placeholder = '';
        foreach ($bindings as $key => $value) {
            $columns[] = $key;
            $sql_placeholder .= $sql_placeholder ? ', ?' : '?';
            $this->bindings['params'][] = $value;
        }
        return 'INSERT INTO ' . $this->bindings['from']['table'] . ' (' . implode(', ', $columns) . ') VALUES (' . $sql_placeholder . ')';
    }

    private function resloveUpdate($bindings = [])
    {
        $sql_placeholder = '';
        foreach ($bindings as $key => $value) {
            $sql_placeholder .= $sql_placeholder ? ", $key = ?" : "$key = ?";
            $this->bindings['params'][] = $value;
        }
        return 'UPDATE ' . $this->bindings['from']['table'] . ' SET ' . $sql_placeholder;
    }

    private function resloveDelete()
    {
        return 'DELETE FROM ' . $this->bindings['from']['table'];
    }

    private function resloveTable(&$sql)
    {
        if (empty($this->bindings['from'])) {
            return '';
        }
        $sql .= ' FROM ' . $this->bindings['from']['table'] . ($this->bindings['from']['as'] ? ' AS ' . $this->bindings['from']['as'] : '');
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

    private function resloveWhere(&$sql, $bindings = [], $isNested = false)
    {
        $bindings = empty($bindings) ? $this->bindings['where'] : $bindings;
        if (empty($this->bindings['where'])) {
            return '';
        }
        if(!$isNested) {
            $sql .= ' WHERE ';
        }
        foreach ($bindings as $idx => $where) {
            if ($where['type'] === 'nested') {
                $sql_nested = '';
                $subWhere = $this->resloveWhere($sql_nested, $where['query'], true);
                $sql .= $where['boolean'] . '(' . $subWhere . ')';
            } elseif ($where['type'] === 'raw') {
                $sql .= "{$where['boolean']}{$where['sql']}";
            } elseif ($where['type'] === 'array') {
                $sql_placeholder = '';
                foreach ($where['value'] as $key => $value) {
                    $sql_placeholder .= $sql_placeholder ? ', ?' : '?';
                }
                $sql .= "{$where['boolean']}{$where['column']} {$where['operator']} ($sql_placeholder)";
                $this->bindings['params'][] = $where['value'];
            }  elseif ($where['type'] === 'between') {
                $sql .= "{$where['boolean']}{$where['column']} {$where['operator']} ? AND ?";
                list($value1, $value2) = array_pad($where['value'], 2, null);
                $this->bindings['params'][] = $value1;
                $this->bindings['params'][] = $value2;
            } else {
                $sql .= "{$where['boolean']}{$where['column']} {$where['operator']} ?";
                $this->bindings['params'][] = $where['value'];
            }
        }
        return $sql;
    }

    private function resloveUnion(&$sql)
    {
        if (empty($this->bindings['union'])) {
            return '';
        }
        $sql = "($sql)";
        foreach ($this->bindings['union'] as $union) {
            $sql .= " UNION " . ($union['all'] ? 'ALL ' : '') . "({$union['query']})";
        }
        return $sql;
    }

    private function resloveGroupBy(&$sql)
    {
        if (empty($this->bindings['groupBy'])) {
            return '';
        }
        $sql .= ' GROUP BY ' . implode(', ', $this->bindings['groupBy']);
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

    private function resloveRelations(Collection $data, $type = 'GET')
    {
        if (
            empty($this->bindings['relations']) ||
            $data->isEmpty() ||
            !is_array($data) ||
            !is_object($data)
        ) {
            return $data;
        }
        $original_data = clone $data;
        $map = $type === 'GET' ? 'map' : 'mapFirst';
        if($type === 'GET') $this->resloveRelationsNotUseQueryN1($original_data);
        $data = $data->{$map}(function ($item) {
            $keys = get_object_vars($item);
            $relationData = array_filter($this->bindings['relations'], function($item) use ($keys) {
                return isset($keys[$item['current_key']['name']]);
            });
            foreach ($relationData as $idx => $relation) {
                $name = $relation['name'];
                $current_key = $relation['current_key']['name'];
                $foreign_key = $relation['foreign_key']['name'];
                if (!empty($relation[$name])) {
                    $values = $relation[$name];
                    $keyValue = in_array($relation['relation'], [
                        'BELONG_TO',
                        'HAS_ONE'
                    ]) ? 'value':'values';
                    $item->{$name} = collection()->set($values)->filter(function ($value) use ($item, $foreign_key, $current_key) {
                        if (is_array($value->{$foreign_key})) {
                            $ids = $value->{$foreign_key};
                            return in_array($item->{$current_key}, $ids);
                        } else {
                            return $item->{$current_key} === $value->{$foreign_key};
                        }
                    })->{$keyValue}();
                } else {
                    $current_key_val = $item->{$current_key};
                    $item->{$name} = $this->resloveRelationsQuery(
                        $relation['related'],
                        $relation['table_3rd'],
                        $current_key,
                        $current_key_val,
                        $relation['foreign_key']['name'],
                        $relation['foreign_key2']['name'],
                        $relation['query'],
                        $relation['columns'],
                        $relation['relation']
                    );
                }
            }
            return $item;
        });
        return $data;
    }

    private function resloveRelationsNotUseQueryN1($original_data)
    {
        $relationNotUseQueryN1 = array_filter($this->bindings['relations'], function($item) {
            return !$item['useN1Query'];
        });
        foreach ($relationNotUseQueryN1 as $idx => $relation) {
            $current_key = $relation['current_key']['name'];
            $current_key_val = $original_data->map(function ($item) use ($current_key) {
                $keys = get_object_vars($item);
                if (isset($keys[$current_key])) {
                    return $item->{$current_key};
                }
                return 0;
            })->filter(fn ($item) => $item > 0)->toArray();
            if (!empty($current_key_val)) {
                $this->bindings['relations'][$idx][$relation['name']] = $this->resloveRelationsQuery(
                    $relation['related'],
                    $relation['table_3rd'],
                    $current_key,
                    $current_key_val,
                    $relation['foreign_key']['name'],
                    $relation['foreign_key2']['name'],
                    $relation['query'],
                    $relation['columns'],
                    $relation['relation']
                );
            }
        }
    }

    private function getTableRelation($related)
    {
        if (class_exists($related)) {
            return new $related();
        }
        $model = new QueryBuilder();
        return $model->from($related);
    }

    private function resloveRelationsQuery(
        $related,
        $table_3rd,
        $current_key_name,
        $current_key_val,
        $foreign_key,
        $foreign_key2 = null,
        $queryBuilder = null,
        $columns = null,
        $reletion = 'HAS_ONE'
    ) {
        $related = $this->getTableRelation($related);
        if (empty($current_key_val)) {
            return [];
        }
        $whereName = 'where';
        $valueName = 'value';
        if (is_array($current_key_val)) {
            $whereName = 'whereIn';
            $valueName = 'values';
        }
        if (
            $reletion === 'HAS_ONE' ||
            $reletion === 'BELONG_TO' ||
            $reletion === 'HAS_MANY'
        ) {
            if ($reletion === 'HAS_MANY') {
                $valueName = 'values';
            }
            $query = $related->{$whereName}($foreign_key, $current_key_val);
            $query = $query->when(!is_null($columns), function ($builder) use ($columns) {
                if (is_string($columns)) {
                    $columns = explode(', ', $columns);
                }
                if (in_array($foreign_key, $columns)) {
                    $columns[] = $foreign_key;
                }
                $builder->select($columns);
            })->when($queryBuilder instanceof \Closure, fn ($builder) => $queryBuilder($builder));
            return $query->get()->{$valueName}();
        } elseif ($reletion === 'MANY_TO_MANY') {
            return $this->resloveRelationsMany(
                $table_3rd,
                $whereName,
                $current_key_name,
                $current_key_val,
                $foreign_key,
                $foreign_key2,
                $related,
                $columns,
                $queryBuilder
            );
        } elseif ($reletion === 'BELONGS_TO_MANY') {
            return $this->resloveRelationsMany(
                $table_3rd,
                $whereName,
                $current_key_name,
                $current_key_val,
                $foreign_key,
                $foreign_key2,
                $related,
                $columns,
                $queryBuilder
            );
        }
    }

    private function resloveRelationsMany($table_3rd, $whereName, $current_key_name, $current_key_val, $foreign_key, $foreign_key2, $related, $columns, $queryBuilder)
    {
        $table_3rd = $this->getTableRelation($table_3rd);
        $data_table_3rd = $table_3rd->{$whereName}($foreign_key, $current_key_val)
            ->get()
            ->toArray();
        $id_joins = collection($data_table_3rd)->dataColumn($foreign_key2)->toArray();
        if (empty($id_joins)) {
            return [];
        }
        $query = $related->whereIn($current_key_name, $id_joins);
        $query = $query->when(!is_null($columns), function ($builder) use ($columns) {
            if (is_string($columns)) {
                $columns = explode(', ', $columns);
            }
            if (in_array($current_key_name, $columns)) {
                $columns[] = $current_key_name;
            }
            $builder->select($columns);
        })->when($queryBuilder instanceof \Closure, fn ($builder) => $queryBuilder($builder));
        if (is_array($current_key_val)) {
            $data = $query->get()->map(function ($item) use (
                $data_table_3rd,
                $current_key_name,
                $foreign_key,
                $foreign_key2
            ) {
                $item->{$foreign_key} = collection($data_table_3rd)->filter(function ($value) use ($item, $current_key_name, $foreign_key2) {
                    return $item->{$current_key_name} == $value->{$foreign_key2};
                })->dataColumn($foreign_key)->toArray();
                return $item;
            })->values();
            return $data;
        }
        return $query->get()->values();
    }

    private function resloveAttribute(&$item, $type = 'SET')
    {
        if (is_null($this->model)) {
            return $item;
        }
        $is_array = is_array($item);
        $keys = $is_array ? $item : get_object_vars($item);
        $attribute = $type === 'SET' || $type === 'UPDATE' ? 'setAttributes' : 'getAttributes';
        foreach ($keys as $key) {
            if (is_numeric($key) || is_object($key) || is_array($key) || is_null($key)) {
                continue;
            }
            $method = $attribute.ucfirst($key);
            if (method_exists($this->model, $method)) {
                if ($is_array) {
                    $item[$key] = $this->model->{$method}($item[$key]);
                } else {
                    $item->{$key} = $this->model->{$method}($item->{$key});
                }
            }
        }

        if ($type === 'GET') {
            if (!empty($this->bindings['variables']['hidden'])) {
                foreach ($this->bindings['variables']['hidden'] as $key_hidden) {
                    if (array_key_exists($key_hidden,(array)$item)) {
                        if ($is_array) {
                            unset($item[$key_hidden]);
                        } else {
                            unset($item->{$key_hidden});
                        }
                    }
                }
            }
        } else if ($type === 'SET') {
            if (
                !empty($this->bindings['variables']['time_auto']) &&
                !empty($this->bindings['variables']['date_updated'])
            ) {
                $date_created = $this->bindings['variables']['date_created'];
                if ($is_array) {
                    $item[$date_created] = date('Y-m-d H:i:s');
                } else {
                    $item->{$date_created} = date('Y-m-d H:i:s');
                }
            }
        } else if ($type === 'UPDATE') {
            if (
                !empty($this->bindings['variables']['time_auto']) &&
                !empty($this->bindings['variables']['date_updated'])
            ) {
                $date_updated = $this->bindings['variables']['date_updated'];
                if ($is_array) {
                    $item[$date_updated] = date('Y-m-d H:i:s');
                } else {
                    $item->{$date_updated} = date('Y-m-d H:i:s');
                }
            }
        }
        return $item;
    }

    public function resloveSoftDelete()
    {
        $bindings = [];
        if (is_null($this->model)) {
            return $this;
        }
        if (method_exists($this->model, 'softDeleteField')) {
            $data = $this->model->softDeleteField();
            if (isTwoDimensionalArray($data)) {
                foreach ($data as $key => $value) {
                    $bindings[$value['field']] = $data['value'];
                }
            } else {
                $bindings[$data['field']] = $data['value'];
            }
        }
        return $bindings;
    }

    private function clearBindings($clearAll = false)
    {
        foreach ($this->bindings as $key => $value) {
           if(!empty($this->bindings[$key])) {
               if ($clearAll) {
                   $this->bindings[$key] = [];
               } else if (
                   $key !== 'variables' &&
                   $key !== 'relations' &&
                   $key !== 'params'
               ) {
                   $this->bindings[$key] = [];
               }
           }
        }
    }

}