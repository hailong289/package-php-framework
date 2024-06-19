<?php
namespace System\Traits;

trait QueryBuilder
{
    private static $tableName = '';
    private static $where = '';
    private static $select = '*';
    private static $orderBy = '';
    private static $operator = '';
    private static $join = '';
    private static $on = '';
    private static $page = '';
    private static $limit = '';
    private static $subQuery = '';
    private static $union = '';
    private static $groupBy = '';
    // start query sub
    private static $startSub = false;
    private static $tableNameSub = '';
    private static $whereSub = '';
    private static $selectSub = '';
    private static $orderBySub = '';
    private static $operatorSub = '';
    private static $joinSub = '';
    private static $onSub = '';
    private static $pageSub = '';
    private static $limitSub = '';
    private static $groupBySub = '';
    private static $sqlSub = '';

    private static function startQuerySub(){ self::$startSub = true; }
    private static function endQuerySub(){ self::$startSub = false; self::resetSub(); }
    private static function isQuerySub(){ return self::$startSub; }

    public function table($tableName)
    {
        if(self::isQuerySub()) {
            self::$tableNameSub = $tableName;
            return $this;
        }
        self::$tableName = $tableName;
        return $this;
    }

    public function from($tableName)
    {
        if(self::isQuerySub()) {
            self::$tableNameSub = $tableName;
            return $this;
        }
        self::$tableName = $tableName;
        return $this;
    }

    public function subQuery($sql, $name)
    {
        if(self::isQuerySub()) {
            return $this;
        }
        if(empty($name)) throw new \RuntimeException("table subQuery is not null", 500);
        self::$tableName = "($sql) as $name";
        return $this;
    }

    public function union($sql)
    {
        if(self::isQuerySub()) {
            return $this;
        }
        self::$union = " UNION $sql";
        return $this;
    }

    public function union_all($sql)
    {
        if(self::isQuerySub()) {
            return $this;
        }
        self::$union = " UNION ALL $sql";
        return $this;
    }

    public function where($field, $compare = '=', $value = '')
    {
        if (is_callable($field)) {
            self::startQuerySub();
            $field($this);
            $operator = self::operator('AND');
            $subWhere = self::$whereSub;
            self::$where .= "{$operator}({$subWhere})";
            self::endQuerySub();
            return $this;
        }
        if(self::isQuerySub()) {
            $operator = self::operator('AND', true);
            if(empty($value)) {
                $value = $compare;
                $compare = '=';
            }
            $value = (is_numeric($value) ? $value:"'".$value."'");
            self::$whereSub .= "{$operator}{$field} {$compare} {$value}";
            return $this;
        }
        $operator = self::operator('AND');
        if(empty($value)) {
            $value = $compare;
            $compare = '=';
        }
        $value = (is_numeric($value) ? $value:"'".$value."'");
        self::$where .= "{$operator}{$field} {$compare} {$value}";
        return $this;
    }

    public function orWhere($field, $compare = '=', $value = '')
    {
        if (is_callable($field)) {
            self::startQuerySub();
            $field($this);
            $operator = self::operator('OR');
            $subWhere = self::$whereSub;
            self::$where .= "{$operator}({$subWhere})";
            self::endQuerySub();
            return $this;
        }
        if(self::isQuerySub()) {
            $operator = self::operator('OR', true);
            if(empty($value)) {
                $value = $compare;
                $compare = '=';
            }
            $value = (is_numeric($value) ? $value:"'".$value."'");
            self::$whereSub .= "{$operator}{$field} {$compare} {$value}";
            return $this;
        }
        $operator = self::operator("OR");
        if(empty($value)) {
            $value = $compare;
            $compare = '=';
        }
        $value = (is_numeric($value) ? $value:"'".$value."'");
        self::$where .= "{$operator}{$field} {$compare} {$value}";
        return $this;
    }

    public function whereLike($field, $value)
    {
        if(self::isQuerySub()) {
            $operator = self::operator('AND', true);
            $value = (is_numeric($value) ? $value:"'".$value."'");
            self::$whereSub .= "{$operator}{$field} LIKE {$value}";
            return $this;
        }
        $operator = self::operator("AND");
        $value = (is_numeric($value) ? $value:"'".$value."'");
        self::$where .= "{$operator}{$field} LIKE {$value}";
        return $this;
    }

    public function orWhereLike($field, $value)
    {
        if(self::isQuerySub()) {
            $operator = self::operator('OR', true);
            $value = (is_numeric($value) ? $value:"'".$value."'");
            self::$whereSub .= "{$operator}{$field} LIKE {$value}";
            return $this;
        }
        $operator = self::operator("OR");
        $value = (is_numeric($value) ? $value:"'".$value."'");
        self::$where .= "{$operator}{$field} LIKE {$value}";
        return $this;
    }

    public function whereIn($field, array $value)
    {
        if(!is_array($value)) {
            throw new \RuntimeException("Params of {$field} is not array function whereIn", 500);
        }
        if(self::isQuerySub()) {
            $operator = self::operator('AND', true);
            $value = implode(',', $value);
            self::$whereSub .= "{$operator}{$field} IN ({$value})";
            return $this;
        }
        $operator = self::operator("AND");
        $value = implode(',', $value);
        self::$where .= "{$operator}{$field} IN ({$value})";
        return $this;
    }

    public function orWhereIn($field, $value)
    {
        if(!is_array($value)) {
            throw new \RuntimeException("Params of {$field} is not array function whereIn", 500);
        }
        if(self::isQuerySub()) {
            $operator = self::operator('OR', true);
            $value = implode(',', $value);
            self::$whereSub .= "{$operator}{$field} IN ({$value})";
            return $this;
        }
        $operator = self::operator("OR");
        $value = implode(',', $value);
        self::$where .= "{$operator}{$field} IN ({$value})";
        return $this;
    }

    public function whereNotIn($field, $value)
    {
        if(!is_array($value)) {
            throw new \RuntimeException("Params of {$field} is not array function whereNotIn", 500);
        }
        if(self::isQuerySub()) {
            $operator = self::operator('AND', true);
            $value = implode(',', $value);
            self::$whereSub .= "{$operator}{$field} NOT IN ({$value})";
            return $this;
        }
        $operator =  self::operator("AND");
        $value = implode(',', $value);
        self::$where .= "{$operator}{$field} NOT IN ({$value})";
        return $this;
    }

    public function orWhereNotIn($field, $value)
    {
        if(!is_array($value)) {
            throw new \RuntimeException("Params of {$field} is not array function whereNotIn", 500);
        }
        if(self::isQuerySub()) {
            $operator = self::operator('OR', true);
            $value = implode(',', $value);
            self::$whereSub .= "{$operator}{$field} NOT IN ({$value})";
            return $this;
        }
        $operator =  self::operator("OR");
        $value = implode(',', $value);
        self::$where .= "{$operator}{$field} NOT IN ({$value})";
        return $this;
    }

    public function whereBetween($field, $value)
    {
        if(!is_array($value)) {
            throw new \RuntimeException("Params of {$field} is not array function whereBetween", 500);
        }
        if(count($value) > 2) {
            throw new \RuntimeException("The value in the array is more than 2 function whereBetween", 500);
        }
        if(self::isQuerySub()) {
            $operator = self::operator('OR', true);
            self::$whereSub .= "{$operator}{$field} BETWEEN '{$value[0]}' AND '{$value[1]}'";
            return $this;
        }
        $operator = self::operator("AND");
        self::$where .= "{$operator}{$field} BETWEEN '{$value[0]}' AND '{$value[1]}'";
        return $this;
    }

    public function whereRaw($sql) {
        if(self::isQuerySub()) {
            $operator = self::operator('AND', true);
            self::$whereSub .= "{$operator}{$sql}";
            return $this;
        }
        $operator = self::operator("AND");
        self::$where .= "{$operator}{$sql}";
        return $this;
    }

    public function orWhereRaw($sql) {
        if(self::isQuerySub()) {
            $operator = self::operator('OR', true);
            self::$whereSub .= "{$operator}{$sql}";
            return $this;
        }
        $operator = self::operator("OR");
        self::$where .= "{$operator}{$sql}";
        return $this;
    }

    public function select($field){
        if(self::isQuerySub()) {
            $field = (is_array($field)) ? implode(", ", $field):$field;
            self::$selectSub = $field;
            return $this;
        }
        $field = (is_array($field)) ? implode(", ", $field):$field;
        self::$select = $field;
        return $this;
    }

    public function orderBy($field, $orderBy = 'ASC'){
        if(self::isQuerySub()) {
            return $this;
        }
        self::$orderBy = " ORDER BY {$field} {$orderBy} ";
        return $this;
    }

    public function join($table, $function = ''){
        if(self::isQuerySub()) {
            return $this;
        }
        if (empty(self::$join)) {
            self::$join = " INNER JOIN {$table}";
        } else {
            self::$join .= " INNER JOIN {$table}";
        }
        if (is_callable($function)) {
            $function($this);
        }
        return $this;
    }

    public function leftJoin($table, $function = ''){
        if(self::isQuerySub()) {
            return $this;
        }
        if (empty(self::$join)) {
            self::$join = " LEFT JOIN {$table}";
        } else {
            self::$join .= " LEFT JOIN {$table}";
        }
        if (is_callable($function)) {
            $function($this);
        }
        return $this;
    }

    public function rightJoin($table, $function = ''){
        if(self::isQuerySub()) {
            return $this;
        }
        if (empty(self::$join)) {
            self::$join = " RIGHT JOIN {$table}";
        } else {
            self::$join .= " RIGHT JOIN {$table}";
        }
        if (is_callable($function)) {
            $function($this);
        }
        return $this;
    }

    public function on($field1, $compare, $field2, $operator = ''){
        if(self::isQuerySub()) {
            return $this;
        }
        if(!empty(self::$on)){
            $operator = empty($operator) ? "AND":$operator;
            self::$operator = " {$operator} ";
        }else{
            self::$operator = " ON ";
        }
        $operator = self::$operator;
        self::$join .= "{$operator} {$field1} {$compare} {$field2}";
        return $this;
    }

    public function groupBy($field){
        if(self::isQuerySub()) {
            return $this;
        }
        $field = is_array($field) ? implode(',',$field):$field;
        self::$groupBy = " GROUP BY {$field}";
        return $this;
    }

    public function page($page){
        if(self::isQuerySub()) {
            return $this;
        }
        self::$page = $page;
        return $this;
    }

    public function limit($limit){
        if(self::isQuerySub()) {
            return $this;
        }
        self::$limit = $limit;
        return $this;
    }


    public function delete(){
        $sql = self::sqlQuery(true);
        $query = $this->query($sql);
        if (!empty($query)) {
            return true;
        }
        return false;
    }

    public function toSqlRaw() {
        $sql = self::sqlQuery();
        return $sql;
    }

    public function showSqlRaw() {
        $sql = self::sqlQuery();
        logs()->dump($sql);
    }


    public function clone() {
        $sql = self::sqlQuery();
        return $sql;
    }

    private static function sqlQuery($is_delete = false, $query = null){
        $select = self::$select;
        $tableName = self::$tableName ? self::$tableName:static::$tableName; // ko có sẽ lấy bên model
        $join = self::$join;
        $where = self::$where;
        $orderBy = self::$orderBy;
        $groupBy = self::$groupBy;
        $fieldTable = static::$field ?? '';
        $offset = is_numeric(self::$page) && is_numeric(self::$limit) ? ' OFFSET '.self::$page * self::$limit:'';
        $limit = is_numeric(self::$limit) ? " LIMIT ".self::$limit:'';
        $union = self::$union;

        if (empty($select)) {
            if (empty($fieldTable)) {
                $fieldTable = '*';
            }else{
                $fieldTable = implode(',', $fieldTable);
            }
            $select = $fieldTable;
        }

        if(!empty($query)) { // use count, sum
            $select = $query;
        }

        if ($is_delete) {
            $sql = "DELETE FROM {$tableName}{$where}{$limit}";
            $sql = trim($sql);
            self::reset();
            return $sql;
        }

        $sql = "SELECT {$select} FROM {$tableName}{$join}
        {$where}{$groupBy}{$orderBy}{$limit}{$offset}{$union}";
        $sql = trim($sql);
        self::reset();
        return $sql;
    }

    public function create($data){
        $tableName = self::$tableName ? self::$tableName:static::$tableName;
        $fieldTable = static::$field ?? [];
        $fieldTableNone = [];
        if(!empty($data)){
            $field = '';
            $value = '';
            foreach($data as $key=>$val){
                if (!in_array($key, $fieldTable)) {
                    $fieldTableNone[] = $key;
                }
                $val = self::setAttribute($key, $val);
                $field .= $key . ',';
                $value .= "'".$val."'". ",";
            }
            if (isset(static::$times_auto) && static::$times_auto) {
                $date_create = static::$date_create ?? 'date_created';
                $now = date('Y-m-d H:i:s');
                $field .= "{$date_create},";
                $value .= "'".$now."'". ",";
            }
            if(count($fieldTableNone) > 0){
                $class = get_class(new static());
                $fieldTableNone = implode(',', $fieldTableNone);
                throw new \RuntimeException("Missing $fieldTableNone in fieldTable class $class", 500);
            }
            $field = rtrim($field, ',');
            $value = rtrim($value, ',');
            $sql = "INSERT INTO $tableName($field) VALUES ($value)";
            $result = $this->query($sql, true);
            self::reset();
            if($result){
                return self::findById($result)->value();
            }
            return false;
        }
    }

    public function insert($data){
        $tableName = self::$tableName ? self::$tableName:static::$tableName; // ko có sẽ lấy bên model
        if(!empty($data)){
            $field = '';
            $value = '';
            foreach($data as $key=>$val){
                $val = self::setAttribute($key, $val);
                $field .= $key . ',';
                $value .= "'".$val."'". ",";
            }
            if (isset(static::$times_auto) && static::$times_auto) {
                $date_create = static::$date_create ?? 'date_created';
                $now = date('Y-m-d H:i:s');
                $field .= "{$date_create},";
                $value .= "'".$now."'". ",";
            }
            $field = rtrim($field, ',');
            $value = rtrim($value, ',');
            $sql = "INSERT INTO $tableName($field) VALUES ($value)";
            $status = $this->query($sql);
            self::reset();
            if($status){
                return true;
            }
            return false;
        }
    }

    public function insertLastId($data){
        $tableName = self::$tableName ? self::$tableName:static::$tableName; // ko có sẽ lấy bên model
        if(!empty($data)){
            $field = '';
            $value = '';
            foreach($data as $key=>$val){
                $val = self::setAttribute($key, $val);
                $field .= $key . ',';
                $value .= "'".$val."'". ",";
            }
            if (isset(static::$times_auto) && static::$times_auto) {
                $date_create = static::$date_create ?? 'date_created';
                $now = date('Y-m-d H:i:s');
                $field .= "{$date_create},";
                $value .= "'".$now."'". ",";
            }
            $field = rtrim($field, ',');
            $value = rtrim($value, ',');
            $sql = "INSERT INTO $tableName($field) VALUES ($value)";
            $result = $this->query($sql, true);
            self::reset();
            if($result){
                return $result;
            }
            return false;
        }
    }

    public function update($data, $fieldOrId = null){
        $tableName = self::$tableName ? self::$tableName:static::$tableName; // ko có sẽ lấy bên model
        if(!empty($data)){
            $compare = '';
            foreach($data as $key=>$val){
                $val = self::setAttribute($key, $val);
                $compare .= $key." = '".$val."', ";
            }
            if (isset(static::$times_auto) && static::$times_auto) {
                $date_update = static::$date_update ?? 'date_updated';
                $now = date('Y-m-d H:i:s');
                $compare .= $date_update." = '".$now."', ";
            }
            $where = '';
            if(empty($fieldOrId)) {
                $where = self::$where;
            } else {
                if(is_array($fieldOrId)){
                    foreach ($fieldOrId as $key=>$value){
                        if (empty($where)) {
                            $where = " WHERE {$key} = '$value'";
                        } else {
                            $where .= " AND {$key} = '$value'";
                        }
                    }
                }else{
                    $where = " WHERE id = $fieldOrId";
                }
            }
            $compare = rtrim($compare, ", ");
            $sql = "UPDATE {$tableName} SET {$compare}{$where}";
            $status = $this->query($sql);
            self::reset();
            if($status){
                return true;
            }
            return false;
        }
    }

    public function updateOrInsert($data, $fieldOrId)
    {
        $tableName = self::$tableName ? self::$tableName:static::$tableName; // ko có sẽ lấy bên model
        $where = '';
        if(is_array($fieldOrId)) {
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
        $has_data = $this->query($sql_has_data)->fetch()->fetch(\PDO::FETCH_OBJ);
        if(!empty($has_data->count)) {
            return self::update($data, $fieldOrId);
        }
        return self::insert($data);
    }

    private static function reset() {
        // reset
        self::$tableName = '';
        self::$where = '';
        self::$select = '*';
        self::$orderBy = '';
        self::$operator = '';
        self::$join = '';
        self::$on = '';
        self::$page = '';
        self::$limit = '';
        self::$union = '';
        self::$groupBy = '';
    }

    private static function resetSub() {
        // reset
        self::$tableNameSub = '';
        self::$whereSub = '';
        self::$selectSub = '';
        self::$orderBySub = '';
        self::$operatorSub = '';
        self::$joinSub = '';
        self::$onSub = '';
        self::$pageSub = '';
        self::$limitSub = '';
        self::$groupBySub = '';
        if (isset(static::$data_relation)) static::$data_relation = [];
    }

    private static function operator($name, $isSub = false) {
        if($isSub) {
            if (!empty(self::$whereSub)) {
                self::$operatorSub = " $name ";
            }
            return self::$operatorSub;
        }
        if (empty(self::$where)) {
            self::$operator = " WHERE ";
        } else {
            self::$operator = " $name ";
        }
        return self::$operator;
    }

    private static function getAttribute($item, $is_array = false)
    {
        $instance = self::instance();
        $keys = array_keys($is_array ? $item:get_object_vars($item));
        foreach ($keys as $key) {
            $name = ucfirst($key);
            if(method_exists($instance, "getAttribute$name")) {
                if($is_array) {
                    $item[$key] = $instance->{"getAttribute$name"}($item[$key]);
                } else {
                    $item->{$key} = $instance->{"getAttribute$name"}($item->{$key});
                }
            }
            if(!empty(static::$hiddenField)) {
                foreach (static::$hiddenField as $key_hidden) {
                    if($is_array) {
                        if(array_key_exists($key_hidden,$item)) unset($item[$key_hidden]);
                    } else {
                        if(array_key_exists($key_hidden,(array)$item)) unset($item->{$key_hidden});
                    }
                }
            }
        }
        return $item;
    }
    private static function setAttribute($key, $val){
        $instance = self::instance();
        if(method_exists($instance, "setAttribute$key")) {
            $val = $instance->{"setAttribute$key"}($val);
        }
        return $val;
    }

}