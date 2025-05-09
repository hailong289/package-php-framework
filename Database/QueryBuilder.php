<?php
namespace Hola\Database;
use Hola\Data\Collection;

class QueryBuilder {

    public static Connection|null $connection = null;
    private $model = null;

    public function __construct() {}

    public static function connect($conn = null, $type = null)
    {
        if (!is_null($conn)) {
            self::$connection = new Connection($conn, $type);
        } else {
            if (is_null(self::$connection)) {
                self::$connection = new Connection();
            }
        }
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
        'params' => [],
        'data' => [],
    ];

    public function setModel($modelCalled, $variables = [])
    {
        $this->model = new $modelCalled();
        $this->setVariables($variables);
    }

    private function setVariables($variables)
    {
        $this->bindings['variables'] = $variables;
        return $this;
    }

    public function reconnectDefault()
    {
        self::$connection = new Connection();
        return $this;
    }

    public function enableQueryLog()
    {
        return self::$connection->enableQueryLog();
    }

    public function getQueryLog()
    {
        return self::$connection->getQueryLog();
    }

    public function beginTransaction()
    {
        return self::$connection->beginTransaction();
    }

    public function commit()
    {
        return self::$connection->commit();;
    }

    public function rollBack()
    {
        return self::$connection->rollBack();
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
            $builder = $this->clone();
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
            $builder = $this->clone();
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

    public function orWhereIn($column, array $value)
    {
        $boolean = !empty($this->bindings['where']) ? ' OR ' : '';
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

    public function orWhereNotIn($column, array $value)
    {
        $boolean = !empty($this->bindings['where']) ? ' OR ' : '';
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
                $sql = $this->resolveSelect();
                $this->resolveTable($sql);
                break;
            case 'INSERT':
                $this->resolveAttribute($bindings, true);
                $this->resolveColumnDate($bindings);
                $sql = $this->resolveInsert($bindings);
                break;
            case 'UPDATE':
                $this->resolveAttribute($bindings, true);
                $this->resolveColumnDate($bindings, true);
                $sql = $this->resolveUpdate($bindings);
                break;
            case 'DELETE':
                $sql = $this->resolveDelete();
                break;
            default:
                $sql = $this->resolveSelect();
                $this->resolveTable($sql);
                break;
        }
        $this->resolveJoin($sql);
        $this->resolveWhere($sql);
        $this->resolveGroupBy($sql);
        $this->resolveHaving($sql);
        $this->resolveOrderBy($sql);
        $this->resolveLimit($sql);
        $this->resolveOffset($sql);
        $this->resolveUnion($sql);
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
        return $clone;
    }

    public function dump() {
        $sql = $this->toSql();
        foreach ($this->bindings['params'] as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $val) {
                    $sql = preg_replace('/\?/', $val, $sql, 1);
                }
            } else {
                $sql = preg_replace('/\?/', $value, $sql, 1);
            }
        }
        logs()->dump($sql);
        return $this;
    }

    public function get(): Collection
    {
        return $this->resolveData($this->toSql(), 'select', function ($selectData) {
            $this->clearBindings(true);
            return $selectData;
        }, $this->bindings['params']);
    }

    public function find($id): Collection
    {
        return $this->where('id', $id)->first();
    }

    public function first(): Collection
    {
        return $this->resolveData($this->toSql(), 'selectOne', function ($selectData) {
            $this->clearBindings(true);
            return $selectData;
        }, $this->bindings['params']);
    }

    public function create($data): Collection
    {
        $object = $this->clone();
        return $this->resolveData($this->toSql('INSERT', $data), 'insertLastId', function ($id) use ($object) {
            $this->clearBindings(true);
            $selectData = $this->resolveCloneObject($object, ['from','variables'])->find($id);
            return $selectData;
        }, $this->bindings['params']);
    }

    public function updateOrInsert($data, $id = null) : bool
    {
        $object = $this->clone();
        $selectData = $this->find($id);
        $callback = function (QueryBuilder $query) use ($object, $selectData, $data, $id) {
            $status = false;
            if ($selectData->isEmpty()) {
                $status = $query->resolveCloneObject($object, ['from','variables'])->insert($data);
            } else {
                $status = $query->resolveCloneObject($object, ['from','variables'])->update($data, $id);
            }
            return $status;
        };
        return $callback($this);
    }

    public function insert($data) : bool
    {
        return $this->resolveData($this->toSql('INSERT', $data), 'insert', function ($selectData, $status) {
            $this->clearBindings(true);
            return $status;
        }, $this->bindings['params']);
    }

    public function insertLastId($data) : int
    {
        return $this->resolveData($this->toSql('INSERT', $data), 'insertLastId', function ($id) {
            $this->clearBindings(true);
            return $id;
        }, $this->bindings['params']);
    }

    public function update($data, $id = null) : bool
    {
        if (!is_null($id)) {
            if (is_array($id)) {
                foreach ($id as $key => $value) {
                    $this->where($key, $value);
                }
            } else {
                $this->where('id', $id);
            }
        }
        return $this->resolveData($this->toSql('UPDATE', $data), 'update', function ($selectData, $status) {
            $this->clearBindings(true);
            return $status;
        },$this->bindings['params']);
    }

    public function save()
    {
        $data = $this->bindings['data'];
        if (isset($data['id'])) {
            $id = $data['id'];
            unset($data['id']);
            return $this->update($data, $id);
        } else {
            return $this->insert($data);
        }
    }

    public function delete($id = null): bool
    {
        if (!is_null($id)) {
            $this->where('id', $id);
        }
        return $this->resolveData($this->toSql('DELETE'), 'delete', function ($selectData, $status) {
            $this->clearBindings(true);
            return $status;
        }, $this->bindings['params']);
    }

    public function softDelete($id = null): bool
    {
        $data = $this->resolveSoftDelete();
        return $this->update($data, $id);
    }

    public function query($sql)
    {
        return self::$connection->query($sql);
    }

    public function getPdo()
    {
        return self::$connection->getPdo();
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
        $builder = $this->clone();
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
        ]);
    }

    private function resolveData($sql, $select, $callback, $bindings = [])
    {
        $status = false;
        $selectData = [];
        $events = [
            'type' => 'event_model',
            'action' => ''
        ];
        switch ($select) {
            case 'select':
                $events['action'] = 'get';
                $select = collection(self::$connection->select($sql, $bindings));
                if ($select->isEmpty()) {
                    app()->event()->trigger('app.model', $events);
                    return $callback(collection([]), $status);
                }
                $selectData = $this->resolveRelations($select);
                $selectData = $selectData->map(fn ($item) => $this->resolveAttribute($item));
                break;
            case 'selectOne':
                $events['action'] = 'get';
                $select = collection(self::$connection->selectOne($sql, $bindings));
                if ($select->isEmpty()) {
                    app()->event()->trigger('app.model', $events);
                    return $callback(collection([]), $status);
                }
                $selectData = $this->resolveRelations($select, 'FIRST');
                $selectData = $selectData->mapFirst(fn ($item) => $this->resolveAttribute($item));
                break;
            case 'insert':
                $events['action'] = 'create';
                app()->event()->trigger('app.model', $events);
                $status = self::$connection->insert($sql, $bindings);
                $events['action'] = 'created';
                break;
            case 'insertLastId':
                $events['action'] = 'create';
                app()->event()->trigger('app.model', $events);
                $selectData = self::$connection->insertLastId($sql, $bindings);
                $events['action'] = 'created';
                break;
            case 'update':
                $events['action'] = 'update';
                app()->event()->trigger('app.model', $events);
                $status = self::$connection->update($sql, $bindings);
                $events['action'] = 'updated';
                break;
            case 'delete':
                $events['action'] = 'delete';
                app()->event()->trigger('app.model', $events);
                $status = self::$connection->delete($sql, $bindings);
                $events['action'] = 'deleted';
                break;
        }
        if (!empty($events['action'])) {
            app()->event()->trigger('app.model', $events);
        }
        return $callback($selectData, $status);
    }

    private function resolveSelect()
    {
        if (!empty($this->bindings['function']['count'])) {
            return 'SELECT '.$this->resolveFunction('count');
        }
        if (!empty($this->bindings['function']['sum'])) {
            return 'SELECT '.$this->resolveFunction('sum');
        }
        if (empty($this->bindings['select'])) {
            return 'SELECT *';
        }
        return 'SELECT ' . implode(', ', $this->bindings['select']);
    }

    private function resolveFunction($function)
    {
        $name = $this->bindings['function'][$function]['name'];
        $alias = $this->bindings['function'][$function]['alias'];
        return "$function($name) AS $alias";
    }

    private function resolveInsert($bindings = [])
    {
        $sql_placeholder = '';
        foreach ($bindings as $key => $value) {
            $columns[] = $key;
            $sql_placeholder .= $sql_placeholder ? ', ?' : '?';
            $this->bindings['params'][] = $value;
        }
        return 'INSERT INTO ' . $this->bindings['from']['table'] . ' (' . implode(', ', $columns) . ') VALUES (' . $sql_placeholder . ')';
    }

    private function resolveUpdate($bindings = [])
    {
        $sql_placeholder = '';
        foreach ($bindings as $key => $value) {
            $sql_placeholder .= $sql_placeholder ? ", $key = ?" : "$key = ?";
            $this->bindings['params'][] = $value;
        }
        return 'UPDATE ' . $this->bindings['from']['table'] . ' SET ' . $sql_placeholder;
    }

    private function resolveDelete()
    {
        return 'DELETE FROM ' . $this->bindings['from']['table'];
    }

    private function resolveTable(&$sql)
    {
        if (empty($this->bindings['from'])) {
            return '';
        }
        $sql .= ' FROM ' . $this->bindings['from']['table'] . ($this->bindings['from']['as'] ? ' AS ' . $this->bindings['from']['as'] : '');
        return $sql;
    }

    private function resolveJoin(&$sql)
    {
        if (empty($this->bindings['join'])) {
            return '';
        }
        foreach ($this->bindings['join'] as $join) {
            $sql .= " {$join['type']} JOIN {$join['table']} ON {$join['first']} {$join['operator']} {$join['second']}";
        }
        return $sql;
    }

    private function resolveWhere(&$sql, $bindings = [], $isNested = false)
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
                $subWhere = $this->resolveWhere($sql_nested, $where['query'], true);
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

    private function resolveUnion(&$sql)
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

    private function resolveGroupBy(&$sql)
    {
        if (empty($this->bindings['groupBy'])) {
            return '';
        }
        $sql .= ' GROUP BY ' . implode(', ', $this->bindings['groupBy']);
        return $sql;
    }

    private function resolveHaving(&$sql)
    {
        if (empty($this->bindings['having'])) {
            return '';
        }
        foreach ($this->bindings['having'] as $having) {
            $sql .= " HAVING {$having['column']} {$having['operator']} {$having['value']} {$having['boolean']}";
        }
        return $sql;
    }

    private function resolveOrderBy(&$sql)
    {
        if (empty($this->bindings['order'])) {
            return '';
        }
        foreach ($this->bindings['order'] as $order) {
            $sql .= " ORDER BY {$order['column']} {$order['direction']}";
        }
        return $sql;
    }

    private function resolveLimit(&$sql)
    {
        if (empty($this->bindings['limit'])) {
            return '';
        }
        $sql .= " LIMIT {$this->bindings['limit']}";
        return $sql;
    }

    private function resolveOffset(&$sql)
    {
        if (empty($this->bindings['offset'])) {
            return '';
        }
        $sql .= " OFFSET {$this->bindings['offset']}";
        return $sql;
    }

    private function resolveRelations(Collection $data, $type = 'GET')
    {
        if (empty($this->bindings['relations']) || $data->isEmpty()) {
            return $data;
        }
        $original_data = clone $data;
        $map = $type === 'GET' ? 'map' : 'mapFirst';
        if($type === 'GET') $this->resolveRelationsNotUseQueryN1($original_data);
        $data = $data->{$map}(function ($item) {
            $keys = get_object_vars($item);
            $relationData = array_filter($this->bindings['relations'], function($item) use ($keys) {
                return isset($keys[$item['current_key']['name']]);
            });
            foreach ($relationData as $idx => $relation) {
                $name = $relation['name'];
                $related_key = $relation['current_key']['name'];
                $foreign_key = $relation['foreign_key']['name'];
                $foreign_key2 = $relation['foreign_key2']['name'];
                if (!empty($relation[$name])) {
                    $values = $relation[$name];
                    $keyValue = in_array($relation['relation'], [
                        'BELONG_TO',
                        'HAS_ONE'
                    ]) ? 'value':'values';
                    $item->{$name} = collection()->set($values)->filter(function ($value) use ($item, $foreign_key, $related_key) {
                        if (is_array($value->{$related_key})) {
                            $ids = $value->{$related_key};
                            return in_array($item->{$foreign_key}, $ids);
                        } else {
                            return $item->{$foreign_key} === $value->{$related_key};
                        }
                    })->{$keyValue}();
                } else {
                    $related_key_val = $item->{$foreign_key};
                    $item->{$name} = $this->resolveRelationsQuery(
                        $relation['related'],
                        $relation['table_3rd'],
                        $related_key,
                        $related_key_val,
                        $foreign_key,
                        $foreign_key2,
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

    private function resolveRelationsNotUseQueryN1(Collection $original_data)
    {
        $values = $original_data->values();
        $relationNotUseQueryN1 = array_filter($this->bindings['relations'], function($item) {
            return !$item['useN1Query'];
        });
        foreach ($relationNotUseQueryN1 as $idx => $relation) {
            $related_key = $relation['current_key']['name'];
            $foreign_key = $relation['foreign_key']['name'];
            $foreign_key2 = $relation['foreign_key2']['name'];
            $related_key_value = collection()->set($values)->map(function ($item) use ($foreign_key) {
                $keys = get_object_vars($item);
                if (isset($keys[$foreign_key])) {
                    return $item->{$foreign_key};
                }
                return 0;
            })->filter(fn ($item) => $item > 0)->toArray();
            if (!empty($related_key_value)) {
                $related_key_value = array_unique($related_key_value);
                $this->bindings['relations'][$idx][$relation['name']] = $this->resolveRelationsQuery(
                    $relation['related'],
                    $relation['table_3rd'],
                    $related_key,
                    $related_key_value,
                    $foreign_key,
                    $foreign_key2,
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

    private function resolveRelationsQuery(
        $related,
        $table_3rd,
        $related_key_name,
        $related_key_val,
        $foreign_key,
        $foreign_key2 = null,
        $queryBuilder = null,
        $columns = null,
        $reletion = 'HAS_ONE'
    ) {
        $related = $this->getTableRelation($related);
        if (empty($related_key_val)) {
            return [];
        }
        $whereName = 'where';
        $valueName = 'value';
        if (is_array($related_key_val)) {
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
            $query = $related->{$whereName}($related_key_name, $related_key_val);
            $query = $query->when(!is_null($columns), function ($builder) use ($columns, $related_key_name) {
                if (is_string($columns)) {
                    $columns = array_filter(array_map('trim', explode(',', $columns)));
                }
                if (!in_array($related_key_name, $columns)) {
                    $columns[] = $related_key_name;
                }
                $builder->select($columns);
            })->when($queryBuilder instanceof \Closure, fn ($builder) => $queryBuilder($builder));
            return $query->get()->{$valueName}();
        } elseif ($reletion === 'MANY_TO_MANY') {
            return $this->resolveRelationsMany(
                $table_3rd,
                $whereName,
                $related_key_name,
                $related_key_val,
                $foreign_key,
                $foreign_key2,
                $related,
                $columns,
                $queryBuilder
            );
        } elseif ($reletion === 'BELONGS_TO_MANY') {
            return $this->resolveRelationsMany(
                $table_3rd,
                $whereName,
                $related_key_name,
                $related_key_val,
                $foreign_key,
                $foreign_key2,
                $related,
                $columns,
                $queryBuilder
            );
        }
    }

    private function resolveRelationsMany($table_3rd, $whereName, $related_key_name, $related_key_val, $foreign_key, $foreign_key2, $related, $columns, $queryBuilder)
    {
        $table_3rd = $this->getTableRelation($table_3rd);
        $data_table_3rd = $table_3rd->{$whereName}($foreign_key, $related_key_val)
            ->get()
            ->toArray();
        $id_joins = collection($data_table_3rd)
            ->dataColumn($foreign_key2)
            ->toArray();

        if (empty($id_joins)) {
            return [];
        }

        $query = $related->whereIn($related_key_name, $id_joins);
        $query = $query->when(!is_null($columns), function ($builder) use (
            $columns,
            $related_key_name
        ) {
            if (is_string($columns)) {
                $columns = array_filter(array_map('trim', explode(',', $columns)));
            }
            if (!in_array($related_key_name, $columns)) {
                $columns[] = $related_key_name;
            }
            $builder->select($columns);
        })->when($queryBuilder instanceof \Closure, fn ($builder) => $queryBuilder($builder));

        if (is_array($related_key_val)) {
            return $query->get()->map(function ($item) use (
                $data_table_3rd,
                $related_key_name,
                $foreign_key,
                $foreign_key2
            ) {
                $item->{$foreign_key} = collection($data_table_3rd)
                    ->filter(function ($value) use (
                        $item,
                        $related_key_name,
                        $foreign_key2
                    ) {
                        return $item->{$related_key_name} == $value->{$foreign_key2};
                    })
                    ->dataColumn($foreign_key)
                    ->toArray();
                return $item;
            })->values();
        }
        return $query->get()->values();
    }

    private function resolveAttribute(&$item, $isSet = false)
    {
        if (is_null($this->model)) {
            return $item;
        }
        $format = is_array($item) ? $item : get_object_vars($item);
        $keys = array_keys($format);
        $attribute = $isSet ? 'setAttributes' : 'getAttributes';
        foreach ($keys as $key) {
            if (is_numeric($key) || is_object($key) || is_array($key) || is_null($key)) {
                continue;
            }
            $method = $attribute.ucfirst($key);
            if (method_exists($this->model, $method)) {
                if (isset($item[$key])) {
                    $item[$key] = $this->model->{$method}($item[$key]);
                } elseif (isset($item->{$key})) {
                    $item->{$key} = $this->model->{$method}($item->{$key});
                }
            }
        }

        if (!$isSet && !empty($this->bindings['variables']['hidden'])) {
            foreach ($this->bindings['variables']['hidden'] as $key_hidden) {
                if (isset($item[$key_hidden])) {
                    unset($item[$key_hidden]);
                } elseif (isset($item->{$key_hidden})) {
                    unset($item->{$key_hidden});
                }
            }
        }
        return $item;
    }
    
    

    private function resolveColumnDate(&$item, $isUpdate = false) {
        if (empty($this->bindings['variables']['time_auto'])) {
            return false;
        }
        $columns = $isUpdate ? 'date_updated' : 'date_created';
        if (!empty($this->bindings['variables'][$columns])) {
            $date_col = $this->bindings['variables'][$columns];
            if (is_array($item)) {
                $item[$date_col] = date('Y-m-d H:i:s');
            } else {
                $item->{$date_col} = date('Y-m-d H:i:s');
            }
        }
        return true;
    }

    private function resolveSoftDelete()
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

    private function resolveCloneObject($object, $keys = [])
    {
        foreach ($object->bindings as $key => $value) {
            if (in_array($key, $keys)) {
                $this->bindings[$key] = $value;
            }
        }
        // destroy object clone
        unset($object);
        return $this;
    }

}