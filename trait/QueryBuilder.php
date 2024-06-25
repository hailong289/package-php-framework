<?php
namespace System\Traits;

use System\Core\Collection;

trait QueryBuilder
{
    private $tableName = '';
    private $where = '';
    private $select = '*';
    private $orderBy = '';
    private $operator = '';
    private $join = '';
    private $on = '';
    private $page = '';
    private $limit = '';
    private $union = '';
    private $groupBy = '';
    // start query sub
    private $startSub = false;
    private $tableNameSub = '';
    private $whereSub = '';
    private $selectSub = '';
    private $orderBySub = '';
    private $operatorSub = '';
    private $joinSub = '';
    private $onSub = '';
    private $pageSub = '';
    private $limitSub = '';
    private $groupBySub = '';
    private static $data_relation = [];

    private function startQuerySub() {
        $this->startSub = true;
    }

    private function endQuerySub() {
        $this->startSub = false;
        $this->resetSub();
    }

    private function isQuerySub() {
        return $this->startSub;
    }

    public function table($tableName)
    {
        if ($this->isQuerySub()) {
            $this->tableNameSub = $tableName;
            return $this;
        }
        $this->tableName = $tableName;
        return $this;
    }

    public function from($tableName)
    {
        if ($this->isQuerySub()) {
            $this->tableNameSub = $tableName;
            return $this;
        }
        $this->tableName = $tableName;
        return $this;
    }

    public function subQuery($sql, $name)
    {
        if ($this->isQuerySub()) {
            return $this;
        }
        if (empty($name)) throw new \RuntimeException("table subQuery is not null", 500);
        $this->tableName = "($sql) as $name";
        return $this;
    }

    public function union($sql)
    {
        if ($this->isQuerySub()) {
            return $this;
        }
        $this->union = " UNION $sql";
        return $this;
    }

    public function union_all($sql)
    {
        if ($this->isQuerySub()) {
            return $this;
        }
        $this->union = " UNION ALL $sql";
        return $this;
    }

    public function where($field, $compare = '=', $value = null)
    {
        if (is_callable($field)) {
            $this->startQuerySub();
            $field($this);
            $operator = $this->operator('AND');
            $subWhere = $this->whereSub;
            $this->where .= "{$operator}({$subWhere})";
            $this->endQuerySub();
            return $this;
        }
        if ($this->isQuerySub()) {
            $operator = $this->operator('AND', true);
            if (is_null($value)) {
                $value = $compare;
                $compare = '=';
            }
            $value = (is_numeric($value) ? $value:"'".$value."'");
            $this->whereSub .= "{$operator}{$field} {$compare} {$value}";
            return $this;
        }
        $operator = $this->operator('AND');
        if (is_null($value)) {
            $value = $compare;
            $compare = '=';
        }
        $value = (is_numeric($value) ? $value:"'".$value."'");
        $this->where .= "{$operator}{$field} {$compare} {$value}";
        return $this;
    }

    public function orWhere($field, $compare = '=', $value = null)
    {
        if (is_callable($field)) {
            $this->startQuerySub();
            $field($this);
            $operator = $this->operator('OR');
            $subWhere = $this->whereSub;
            $this->where .= "{$operator}({$subWhere})";
            $this->endQuerySub();
            return $this;
        }
        if ($this->isQuerySub()) {
            $operator = $this->operator('OR', true);
            if (is_null($value)) {
                $value = $compare;
                $compare = '=';
            }
            $value = (is_numeric($value) ? $value:"'".$value."'");
            $this->whereSub .= "{$operator}{$field} {$compare} {$value}";
            return $this;
        }
        $operator = $this->operator("OR");
        if (is_null($value)) {
            $value = $compare;
            $compare = '=';
        }
        $value = (is_numeric($value) ? $value:"'".$value."'");
        $this->where .= "{$operator}{$field} {$compare} {$value}";
        return $this;
    }

    public function whereLike($field, $value) {
        if ($this->isQuerySub()) {
            $operator = $this->operator('AND', true);
            $value = (is_numeric($value) ? $value:"'".$value."'");
            $this->whereSub .= "{$operator}{$field} LIKE {$value}";
            return $this;
        }
        $operator = $this->operator("AND");
        $value = (is_numeric($value) ? $value:"'".$value."'");
        $this->where .= "{$operator}{$field} LIKE {$value}";
        return $this;
    }

    public function orWhereLike($field, $value)
    {
        if ($this->isQuerySub()) {
            $operator = $this->operator('OR', true);
            $value = (is_numeric($value) ? $value:"'".$value."'");
            $this->whereSub .= "{$operator}{$field} LIKE {$value}";
            return $this;
        }
        $operator = $this->operator("OR");
        $value = (is_numeric($value) ? $value:"'".$value."'");
        $this->where .= "{$operator}{$field} LIKE {$value}";
        return $this;
    }

    public function whereIn($field, array $value)
    {
        if (!is_array($value)) {
            throw new \RuntimeException("Params of {$field} is not array function whereIn", 500);
        }
        if($this->isQuerySub()) {
            $operator = $this->operator('AND', true);
            $value = implode(',', $value);
            $this->whereSub .= "{$operator}{$field} IN ({$value})";
            return $this;
        }
        $operator = $this->operator("AND");
        $value = implode(',', $value);
        $this->where .= "{$operator}{$field} IN ({$value})";
        return $this;
    }

    public function orWhereIn($field, $value)
    {
        if (!is_array($value)) {
            throw new \RuntimeException("Params of {$field} is not array function whereIn", 500);
        }
        if ($this->isQuerySub()) {
            $operator = $this->operator('OR', true);
            $value = implode(',', $value);
            $this->whereSub .= "{$operator}{$field} IN ({$value})";
            return $this;
        }
        $operator = $this->operator("OR");
        $value = implode(',', $value);
        $this->where .= "{$operator}{$field} IN ({$value})";
        return $this;
    }

    public function whereNotIn($field, $value)
    {
        if (!is_array($value)) {
            throw new \RuntimeException("Params of {$field} is not array function whereNotIn", 500);
        }
        if ($this->isQuerySub()) {
            $operator = $this->operator('AND', true);
            $value = implode(',', $value);
            $this->whereSub .= "{$operator}{$field} NOT IN ({$value})";
            return $this;
        }
        $operator =  $this->operator("AND");
        $value = implode(',', $value);
        $this->where .= "{$operator}{$field} NOT IN ({$value})";
        return $this;
    }

    public function orWhereNotIn($field, $value)
    {
        if (!is_array($value)) {
            throw new \RuntimeException("Params of {$field} is not array function whereNotIn", 500);
        }
        if($this->isQuerySub()) {
            $operator = $this->operator('OR', true);
            $value = implode(',', $value);
            $this->whereSub .= "{$operator}{$field} NOT IN ({$value})";
            return $this;
        }
        $operator =  $this->operator("OR");
        $value = implode(',', $value);
        $this->where .= "{$operator}{$field} NOT IN ({$value})";
        return $this;
    }

    public function whereBetween($field, $value) {
        if (!is_array($value)) {
            throw new \RuntimeException("Params of {$field} is not array function whereBetween", 500);
        }
        if (count($value) > 2) {
            throw new \RuntimeException("The value in the array is more than 2 function whereBetween", 500);
        }
        if ($this->isQuerySub()) {
            $operator = $this->operator('OR', true);
            $this->whereSub .= "{$operator}{$field} BETWEEN '{$value[0]}' AND '{$value[1]}'";
            return $this;
        }
        $operator = $this->operator("AND");
        $this->where .= "{$operator}{$field} BETWEEN '{$value[0]}' AND '{$value[1]}'";
        return $this;
    }

    public function whereRaw($sql) {
        if ($this->isQuerySub()) {
            $operator = $this->operator('AND', true);
            $this->whereSub .= "{$operator}{$sql}";
            return $this;
        }
        $operator = $this->operator("AND");
        $this->where .= "{$operator}{$sql}";
        return $this;
    }

    public function orWhereRaw($sql) {
        if ($this->isQuerySub()) {
            $operator = $this->operator('OR', true);
            $this->whereSub .= "{$operator}{$sql}";
            return $this;
        }
        $operator = $this->operator("OR");
        $this->where .= "{$operator}{$sql}";
        return $this;
    }

    public function select($field) {
        if ($this->isQuerySub()) {
            $field = (is_array($field)) ? implode(", ", $field):$field;
            $this->selectSub = $field;
            return $this;
        }
        $field = (is_array($field)) ? implode(", ", $field):$field;
        $this->select = $field;
        return $this;
    }

    public function orderBy($field, $orderBy = 'ASC') {
        if ($this->isQuerySub()) {
            return $this;
        }
        $this->orderBy = " ORDER BY {$field} {$orderBy} ";
        return $this;
    }

    public function join($table, $function = '') {
        if ($this->isQuerySub()) {
            return $this;
        }
        if (empty($this->join)) {
            $this->join = " INNER JOIN {$table}";
        } else {
            $this->join .= " INNER JOIN {$table}";
        }
        if (is_callable($function)) {
            $function($this);
        }
        return $this;
    }

    public function leftJoin($table, $function = '') {
        if ($this->isQuerySub()) {
            return $this;
        }
        if (empty($this->join)) {
            $this->join = " LEFT JOIN {$table}";
        } else {
            $this->join .= " LEFT JOIN {$table}";
        }
        if (is_callable($function)) {
            $function($this);
        }
        return $this;
    }

    public function rightJoin($table, $function = '') {
        if ($this->isQuerySub()) {
            return $this;
        }
        if (empty($this->join)) {
            $this->join = " RIGHT JOIN {$table}";
        } else {
            $this->join .= " RIGHT JOIN {$table}";
        }
        if (is_callable($function)) {
            $function($this);
        }
        return $this;
    }

    public function on($field1, $compare, $field2, $operator = '') {
        if ($this->isQuerySub()) {
            return $this;
        }
        if (!empty($this->on)) {
            $operator = empty($operator) ? "AND":$operator;
            $this->operator = " {$operator} ";
        } else {
            $this->operator = " ON ";
        }
        $operator = $this->operator;
        $this->join .= "{$operator} {$field1} {$compare} {$field2}";
        return $this;
    }

    public function groupBy($field) {
        if ($this->isQuerySub()) {
            return $this;
        }
        $field = is_array($field) ? implode(',',$field):$field;
        $this->groupBy = " GROUP BY {$field}";
        return $this;
    }

    public function page($page) {
        if ($this->isQuerySub()) {
            return $this;
        }
        $this->page = $page;
        return $this;
    }

    public function limit($limit) {
        if($this->isQuerySub()) {
            return $this;
        }
        $this->limit = $limit;
        return $this;
    }


    public function delete() {
        $sql = $this->sqlQuery(true);
        $query = $this->query($sql);
        if (!empty($query)) {
            return true;
        }
        return false;
    }

    public function toSqlRaw() {
        $sql = $this->sqlQuery();
        return $sql;
    }

    public function showSqlRaw() {
        $sql = $this->sqlQuery();
        logs()->dump($sql);
    }


    public function clone() {
        $sql = $this->sqlQuery();
        return $sql;
    }

    private function sqlQuery($is_delete = false, $query = null, $byId = 0){
        $select = $this->select;
        $tableName = $this->tableName;
        $join = $this->join;
        $where = $this->where;
        $orderBy = $this->orderBy;
        $groupBy = $this->groupBy;
        $fieldTable = $this->field ?? '';
        $offset = is_numeric($this->page) && is_numeric($this->limit) ? ' OFFSET '.$this->page * $this->limit:'';
        $limit = is_numeric($this->limit) ? " LIMIT ".$this->limit:'';
        $union = $this->union;

        if (empty($select)) {
            if (empty($fieldTable)) {
                $fieldTable = '*';
            } else {
                $fieldTable = implode(',', $fieldTable);
            }
            $select = $fieldTable;
        }

        if (!empty($query)) { // use count, sum
            $select = $query;
        }

        if ($byId) {
            $sql = "SELECT {$select} FROM {$tableName} WHERE id = '$byId' LIMIT 1";
            $sql = trim($sql);
            $this->reset();
            return $sql;
        }

        if ($is_delete) {
            $sql = "DELETE FROM {$tableName}{$where}{$limit}";
            $sql = trim($sql);
            $this->reset();
            return $sql;
        }

        $sql = "SELECT {$select} FROM {$tableName}{$join}
        {$where}{$groupBy}{$orderBy}{$limit}{$offset}{$union}";
        $sql = trim($sql);
        $this->reset();
        return $sql;
    }

    public function create($data) {
        $tableName = $this->tableName;
        $fieldTable = $this->field ?? [];
        $fieldTableNone = [];
        if (!empty($data)) {
            $field = '';
            $value = '';
            foreach($data as $key=>$val){
                if (!in_array($key, $fieldTable)) {
                    $fieldTableNone[] = $key;
                }
                $val = $this->setAttribute($key, $val);
                $field .= $key . ',';
                $value .= "'".$val."'". ",";
            }
            if (isset($this->times_auto) && $this->times_auto) {
                $date_create = $this->date_create ?? 'date_created';
                $now = date('Y-m-d H:i:s');
                $field .= "{$date_create},";
                $value .= "'".$now."'". ",";
            }
            if (count($fieldTableNone) > 0) {
                $class = get_class($this);
                $fieldTableNone = implode(',', $fieldTableNone);
                throw new \RuntimeException("Missing $fieldTableNone in fieldTable class $class", 500);
            }
            $field = rtrim($field, ',');
            $value = rtrim($value, ',');
            $sql = "INSERT INTO $tableName($field) VALUES ($value)";
            $result = $this->query($sql, true);
            $result = $this->findById($result)->value();
            $this->reset();
            if ($result) {
                return $result;
            }
            return false;
        }
    }

    public function insert($data) {
        $tableName = $this->tableName;
        if (!empty($data)) {
            $field = '';
            $value = '';
            foreach($data as $key=>$val){
                $val = $this->setAttribute($key, $val);
                $field .= $key . ',';
                $value .= "'".$val."'". ",";
            }
            if (isset($this->times_auto) && $this->times_auto) {
                $date_create = $this->date_create ?? 'date_created';
                $now = date('Y-m-d H:i:s');
                $field .= "{$date_create},";
                $value .= "'".$now."'". ",";
            }
            $field = rtrim($field, ',');
            $value = rtrim($value, ',');
            $sql = "INSERT INTO $tableName($field) VALUES ($value)";
            $status = $this->query($sql);
            $this->reset();
            if ($status) {
                return true;
            }
            return false;
        }
    }

    public function insertLastId($data) {
        $tableName = $this->tableName;
        if (!empty($data)) {
            $field = '';
            $value = '';
            foreach($data as $key=>$val){
                $val = $this->setAttribute($key, $val);
                $field .= $key . ',';
                $value .= "'".$val."'". ",";
            }
            if (isset($this->times_auto) && $this->times_auto) {
                $date_create = $this->date_create ?? 'date_created';
                $now = date('Y-m-d H:i:s');
                $field .= "{$date_create},";
                $value .= "'".$now."'". ",";
            }
            $field = rtrim($field, ',');
            $value = rtrim($value, ',');
            $sql = "INSERT INTO $tableName($field) VALUES ($value)";
            $result = $this->query($sql, true);
            $this->reset();
            if ($result) {
                return $result;
            }
            return false;
        }
    }

    public function update($data, $fieldOrId = null) {
        $tableName = $this->tableName;
        if (!empty($data)) {
            $compare = '';
            foreach($data as $key=>$val){
                $val = $this->setAttribute($key, $val);
                $compare .= $key." = '".$val."', ";
            }
            if (isset($this->times_auto) && $this->times_auto) {
                $date_update = $this->date_update ?? 'date_updated';
                $now = date('Y-m-d H:i:s');
                $compare .= $date_update." = '".$now."', ";
            }
            $where = '';
            if (empty($fieldOrId)) {
                $where = $this->where;
            } else {
                if (is_array($fieldOrId)) {
                    foreach ($fieldOrId as $key=>$value){
                        if (empty($where)) {
                            $where = " WHERE {$key} = '$value'";
                        } else {
                            $where .= " AND {$key} = '$value'";
                        }
                    }
                } else {
                    $where = " WHERE id = $fieldOrId";
                }
            }
            $compare = rtrim($compare, ", ");
            $sql = "UPDATE {$tableName} SET {$compare}{$where}";
            $status = $this->query($sql);
            $this->reset();
            if ($status) {
                return true;
            }
            return false;
        }
    }

    public function updateOrInsert($data, $fieldOrId) {
        $tableName = $this->tableName;
        $where = '';
        if (is_array($fieldOrId)) {
            foreach ($fieldOrId as $key=>$value){
                if (empty($where)) {
                    $where = " WHERE {$key} = '$value'";
                } else {
                    $where .= " AND {$key} = '$value'";
                }
            }
        } else {
            $where = " WHERE id = $fieldOrId";
        }
        $sql_has_data = "SELECT count(*) as count FROM {$tableName}{$where} LIMIT 1";
        $has_data = $this->query($sql_has_data)->fetch(\PDO::FETCH_OBJ);
        if (!empty($has_data->count)) {
            return $this->update($data, $fieldOrId);
        }
        return $this->insert($data);
    }

    private function reset() {
        // reset
        $this->tableName = '';
        $this->where = '';
        $this->select = '*';
        $this->orderBy = '';
        $this->operator = '';
        $this->join = '';
        $this->on = '';
        $this->page = '';
        $this->limit = '';
        $this->union = '';
        $this->groupBy = '';
    }

    private function resetSub() {
        // reset
        $this->tableNameSub = '';
        $this->whereSub = '';
        $this->selectSub = '';
        $this->orderBySub = '';
        $this->operatorSub = '';
        $this->joinSub = '';
        $this->onSub = '';
        $this->pageSub = '';
        $this->limitSub = '';
        $this->groupBySub = '';
    }

    private function operator($name, $isSub = false) {
        if ($isSub) {
            if (!empty($this->whereSub)) {
                $this->operatorSub = " $name ";
            }
            return $this->operatorSub;
        }
        if (empty($this->where)) {
            $this->operator = " WHERE ";
        } else {
            $this->operator = " $name ";
        }
        return $this->operator;
    }

    private function getAttribute($item, $is_array = false) {
        $instance = $this->getModel();
        $keys = array_keys($is_array ? $item:get_object_vars($item));
        foreach ($keys as $key) {
            $name = ucfirst($key);
            if (method_exists($instance, "getAttribute$name")) {
                if ($is_array) {
                    $item[$key] = $instance->{"getAttribute$name"}($item[$key]);
                } else {
                    $item->{$key} = $instance->{"getAttribute$name"}($item->{$key});
                }
            }
            if (!empty($this->hiddenField)) {
                foreach ($this->hiddenField as $key_hidden) {
                    if ($is_array) {
                        if (array_key_exists($key_hidden,$item)) {
                            unset($item[$key_hidden]);
                        }
                    } else {
                        if (array_key_exists($key_hidden,(array)$item)) {
                            unset($item->{$key_hidden});
                        }
                    }
                }
            }
        }
        return $item;
    }
    private function setAttribute($key, $val) {
        $instance = $this->getModel();
        if (method_exists($instance, "setAttribute$key")) {
            $val = $instance->{"setAttribute$key"}($val);
        }
        return $val;
    }

    // relation
    public function with($name) {
        $instance = $this->getModel();
        if (is_array($name)) {
            foreach ($name as $key=>$value) {
                if (is_numeric($key)) {
                    $explode = explode(':', $value);
                    $relation = $explode[0] ?? $value;
                    if (method_exists($instance, $relation)) {
                        $data_relation = $instance->{$relation}();
                        self::$data_relation[] = $data_relation;
                        if (!empty($explode[1]) && !empty(self::$data_relation)) {
                            $key_last = array_key_last(self::$data_relation);
                            self::$data_relation[$key_last]['query'] = function ($query) use ($explode) {
                                return $query->select($explode[1]);
                            };
                        }
                    }
                } else {
                    // query
                    $query = $value;
                    $relation = $key;
                    if (method_exists($instance, $relation)) {
                        $data_relation = $instance->{$relation}();
                        self::$data_relation[] = $data_relation;
                        if(!empty(self::$data_relation)) {
                            $key_last = array_key_last(self::$data_relation);
                            self::$data_relation[$key_last]['query'] = $query;
                        }
                    }
                }
            }
            return $this;
        }
        if (method_exists($instance, $name)) {
            $data_relation = $instance->{$name}();
            self::$data_relation[] = $data_relation;
        }
        return $this;
    }

    private function workRelation($data, $type = 'get') {
        if (empty(self::$data_relation)) {
            return false;
        }
        if ($data instanceof Collection) {
            if ($type === 'get') {
                $result = $data->map(function ($item) {
                    $keys = get_object_vars($item);
                    foreach (self::$data_relation as $key => $relation) {
                        $primary_key = $relation['primary_key'];
                        $foreign_key = $relation['foreign_key'];
                        $foreign_key2 = $relation['foreign_key2'];
                        $model = $relation['model'];
                        $model_many = $relation['model_many'];
                        $name = $relation['name'];
                        $name_relation = $relation['relation'];
                        $query = $relation['query'] ?? null;
                        if (isset($keys[$primary_key])) {
                            $item->{$name} =  $this->dataRelation(
                                $name_relation,
                                $model,
                                $model_many,
                                $foreign_key,
                                $foreign_key2,
                                $item->{$primary_key},
                                $query
                            );
                        }
                    }
                    return $item;
                });
                self::$data_relation = []; // reset when successful
                return $result;
            } else {
                $result = $data->mapFirst(function ($item) {
                    $keys = get_object_vars($item);
                    foreach (self::$data_relation as $key => $relation) {
                        $primary_key = $relation['primary_key'];
                        $foreign_key = $relation['foreign_key'];
                        $foreign_key2 = $relation['foreign_key2'] ?? null;
                        $model = $relation['model'];
                        $model_many = $relation['model_many'] ?? null;
                        $name = $relation['name'];
                        $name_relation = $relation['relation'];
                        $query = $relation['query'] ?? null;
                        if (isset($keys[$primary_key])) {
                            $item->{$name} = $this->dataRelation(
                                $name_relation,
                                $model,
                                $model_many,
                                $foreign_key,
                                $foreign_key2,
                                $item->{$primary_key},
                                $query
                            );
                        }
                    }
                    return $item;
                });
                self::$data_relation = []; // reset when successful
                return $result;
            }
        }
    }

    private function dataRelation(
        $relation,
        $model,
        $model_many,
        $foreign_key,
        $foreign_key2,
        $primary_key,
        $query
    ) {
        $instance = $this;
        if ($relation === $this->HAS_MANY) {
            $db_table = class_exists($model) ? (new $model):$this->table($model);
            if (!empty($query)) {
                $db_table = $query($db_table);
            }
            $sql = $db_table->where($foreign_key, $primary_key)->clone();
            $data = $instance->query($sql)->fetchAll(\PDO::FETCH_OBJ);
            return $instance->getCollection($data)->values();
        } else if($relation === $this->BELONG_TO) {
            $db_table = class_exists($model) ? (new $model):$this->table($model);
            if (!empty($query)) {
                $db_table = $query($db_table);
            }
            $sql = $db_table->where($foreign_key, $primary_key)->clone();
            $data = $instance->query($sql)->fetch(\PDO::FETCH_OBJ);
            return $instance->getCollection($data)->value();
        } else if($relation === $this->MANY_TO_MANY) {
            // get id 3rd table
            $db_table_many = class_exists($model_many) ? (new $model_many):$this->table($model_many);
            $sql_tb_3rd =  $db_table_many->where($foreign_key, $primary_key)->clone();
            $data_tb_3rd = $instance->query($sql_tb_3rd)->fetchAll(\PDO::FETCH_OBJ);
            $id_join = $instance->getCollection($data_tb_3rd)->dataColumn($foreign_key2)->values();
            if(!empty($id_join)) {
                $db_table = class_exists($model) ? (new $model):$this->table($model);
                if (!empty($query)) {
                    $db_table = $query($db_table);
                }
                $sql = $db_table->whereIn('id', $id_join)->clone();
                $data = $instance->query($sql)->fetchAll(\PDO::FETCH_OBJ);
                return $instance->getCollection($data)->values();
            }
            return [];
        } else if($relation === $this->BELONG_TO_MANY) {
            // get id 3rd table
            $db_table_many = class_exists($model_many) ? (new $model_many):$this->table($model_many);
            $sql_tb_3rd =  $db_table_many->where($foreign_key, $primary_key)->clone();
            $data_tb_3rd = $instance->query($sql_tb_3rd)->fetchAll(\PDO::FETCH_OBJ);
            $id_join = $instance->getCollection($data_tb_3rd)->dataColumn($foreign_key2)->toArray();
            if(!empty($id_join)) {
                $db_table = class_exists($model) ? (new $model):$this->table($model);
                if (!empty($query)) {
                    $db_table = $query($db_table);
                }
                $sql = $db_table->whereIn('id', $id_join)->clone();
                $data = $instance->query($sql)->fetchAll(\PDO::FETCH_OBJ);
                return $instance->getCollection($data)->values();
            }
            return [];
        } else { // has one
            $db_table = class_exists($model) ? (new $model):$this->table($model);
            if (!empty($query)) {
                $db_table = $query($db_table);
            }
            $sql = $db_table->where($foreign_key, $primary_key)->clone();
            $data = $instance->query($sql)->fetch(\PDO::FETCH_OBJ);
            return $instance->getCollection($data)->value();
        }
    }
}