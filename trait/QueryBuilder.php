<?php
namespace System\Trait;

trait QueryBuilder
{
    private static $tableName = '';
    private static $where = '';
    private static $whereExit = '';
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

    public static function table($tableName)
    {
        if(self::isQuerySub()) {
            self::$tableNameSub = $tableName;
            return self::modelInstance();
        }
        self::$tableName = $tableName;
        return self::modelInstance();
    }

    public static function from($tableName)
    {
        if(self::isQuerySub()) {
            self::$tableNameSub = $tableName;
            return self::modelInstance();
        }
        self::$tableName = $tableName;
        return self::modelInstance();
    }

    public static function subQuery($sql, $name)
    {
        if(self::isQuerySub()) {
            return self::modelInstance();
        }
        if(empty($name)) throw new \RuntimeException("table subQuery is not null", 500);
        self::$tableName = "($sql) as $name";
        return self::modelInstance();
    }

    public static function union($sql)
    {
        if(self::isQuerySub()) {
            return self::modelInstance();
        }
        self::$union = " UNION $sql";
        return self::modelInstance();
    }

    public static function union_all($sql)
    {
        if(self::isQuerySub()) {
            return self::modelInstance();
        }
        self::$union = " UNION ALL $sql";
        return self::modelInstance();
    }

    public static function where($field, $compare = '=', $value = '')
    {
        if (is_callable($field)) {
            self::startQuerySub();
            $field(self::modelInstance());
            $operator = self::operator('AND');
            $subWhere = self::$whereSub;
            self::$where .= "{$operator}({$subWhere})";
            self::endQuerySub();
            return self::modelInstance();
        }
        if(self::isQuerySub()) {
            $operator = self::operator('AND', true);
            if(empty($value)) {
                $value = $compare;
                $compare = '=';
            }
            $value = (is_numeric($value) ? $value:"'".$value."'");
            self::$whereSub .= "{$operator}{$field} {$compare} {$value}";
            return self::modelInstance();
        }
        $operator = self::operator('AND');
        if(empty($value)) {
            $value = $compare;
            $compare = '=';
        }
        $value = (is_numeric($value) ? $value:"'".$value."'");
        self::$where .= "{$operator}{$field} {$compare} {$value}";
        return self::modelInstance();
    }

    public static function orWhere($field, $compare = '=', $value = '')
    {
        if (is_callable($field)) {
            self::startQuerySub();
            $field(self::modelInstance());
            $operator = self::operator('OR');
            $subWhere = self::$whereSub;
            self::$where .= "{$operator}({$subWhere})";
            self::endQuerySub();
            return self::modelInstance();
        }
        if(self::isQuerySub()) {
            $operator = self::operator('OR', true);
            if(empty($value)) {
                $value = $compare;
                $compare = '=';
            }
            $value = (is_numeric($value) ? $value:"'".$value."'");
            self::$whereSub .= "{$operator}{$field} {$compare} {$value}";
            return self::modelInstance();
        }
        $operator = self::operator("OR");
        if(empty($value)) {
            $value = $compare;
            $compare = '=';
        }
        $value = (is_numeric($value) ? $value:"'".$value."'");
        self::$where .= "{$operator}{$field} {$compare} {$value}";
        return self::modelInstance();
    }

    public static function whereLike($field, $value)
    {
        if(self::isQuerySub()) {
            $operator = self::operator('AND', true);
            $value = (is_numeric($value) ? $value:"'".$value."'");
            self::$whereSub .= "{$operator}{$field} LIKE {$value}";
            return self::modelInstance();
        }
        $operator = self::operator("AND");
        $value = (is_numeric($value) ? $value:"'".$value."'");
        self::$where .= "{$operator}{$field} LIKE {$value}";
        return self::modelInstance();
    }

    public static function orWhereLike($field, $value)
    {
        if(self::isQuerySub()) {
            $operator = self::operator('OR', true);
            $value = (is_numeric($value) ? $value:"'".$value."'");
            self::$whereSub .= "{$operator}{$field} LIKE {$value}";
            return self::modelInstance();
        }
        $operator = self::operator("OR");
        $value = (is_numeric($value) ? $value:"'".$value."'");
        self::$where .= "{$operator}{$field} LIKE {$value}";
        return self::modelInstance();
    }

    public static function whereIn($field, array $value)
    {
        if(!is_array($value)) {
            throw new \RuntimeException("Params of {$field} is not array function whereIn", 500);
        }
        if(self::isQuerySub()) {
            $operator = self::operator('AND', true);
            $value = implode(',', $value);
            self::$whereSub .= "{$operator}{$field} IN ({$value})";
            return self::modelInstance();
        }
        $operator = self::operator("AND");
        $value = implode(',', $value);
        self::$where .= "{$operator}{$field} IN ({$value})";
        return self::modelInstance();
    }

    public static function orWhereIn($field, $value)
    {
        if(!is_array($value)) {
            throw new \RuntimeException("Params of {$field} is not array function whereIn", 500);
        }
        if(self::isQuerySub()) {
            $operator = self::operator('OR', true);
            $value = implode(',', $value);
            self::$whereSub .= "{$operator}{$field} IN ({$value})";
            return self::modelInstance();
        }
        $operator = self::operator("OR");
        $value = implode(',', $value);
        self::$where .= "{$operator}{$field} IN ({$value})";
        return self::modelInstance();
    }

    public static function whereNotIn($field, $value)
    {
        if(!is_array($value)) {
            throw new \RuntimeException("Params of {$field} is not array function whereNotIn", 500);
        }
        if(self::isQuerySub()) {
            $operator = self::operator('AND', true);
            $value = implode(',', $value);
            self::$whereSub .= "{$operator}{$field} NOT IN ({$value})";
            return self::modelInstance();
        }
        $operator =  self::operator("AND");
        $value = implode(',', $value);
        self::$where .= "{$operator}{$field} NOT IN ({$value})";
        return self::modelInstance();
    }

    public static function orWhereNotIn($field, $value)
    {
        if(!is_array($value)) {
            throw new \RuntimeException("Params of {$field} is not array function whereNotIn", 500);
        }
        if(self::isQuerySub()) {
            $operator = self::operator('OR', true);
            $value = implode(',', $value);
            self::$whereSub .= "{$operator}{$field} NOT IN ({$value})";
            return self::modelInstance();
        }
        $operator =  self::operator("OR");
        $value = implode(',', $value);
        self::$where .= "{$operator}{$field} NOT IN ({$value})";
        return self::modelInstance();
    }

    public static function whereBetween($field, $value)
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
            return self::modelInstance();
        }
        $operator = self::operator("AND");
        self::$where .= "{$operator}{$field} BETWEEN '{$value[0]}' AND '{$value[1]}'";
        return self::modelInstance();
    }

    public static function whereRaw($sql) {
        if(self::isQuerySub()) {
            $operator = self::operator('AND', true);
            self::$whereSub .= "{$operator}{$sql}";
            return self::modelInstance();
        }
        $operator = self::operator("AND");
        self::$where .= "{$operator}{$sql}";
        return self::modelInstance();
    }

    public static function orWhereRaw($sql) {
        if(self::isQuerySub()) {
            $operator = self::operator('OR', true);
            self::$whereSub .= "{$operator}{$sql}";
            return self::modelInstance();
        }
        $operator = self::operator("OR");
        self::$where .= "{$operator}{$sql}";
        return self::modelInstance();
    }

    public static function select($field){
        if(self::isQuerySub()) {
            $field = (is_array($field)) ? implode(", ", $field):$field;
            self::$selectSub = $field;
            return self::modelInstance();
        }
        $field = (is_array($field)) ? implode(", ", $field):$field;
        self::$select = $field;
        return self::modelInstance();
    }

    public static function orderBy($field, $orderBy = 'ASC'){
        if(self::isQuerySub()) {
            return self::modelInstance();
        }
        self::$orderBy = " ORDER BY {$field} {$orderBy} ";
        return self::modelInstance();
    }

    public static function join($table, $function = ''){
        if(self::isQuerySub()) {
            return self::modelInstance();
        }
        if (empty(self::$join)) {
            self::$join = " INNER JOIN {$table}";
        } else {
            self::$join .= " INNER JOIN {$table}";
        }
        if (is_callable($function)) {
            $function(self::modelInstance());
        }
        return self::modelInstance();
    }

    public static function leftJoin($table, $function = ''){
        if(self::isQuerySub()) {
            return self::modelInstance();
        }
        if (empty(self::$join)) {
            self::$join = " LEFT JOIN {$table}";
        } else {
            self::$join .= " LEFT JOIN {$table}";
        }
        if (is_callable($function)) {
            $function(self::modelInstance());
        }
        return self::modelInstance();
    }

    public static function rightJoin($table, $function = ''){
        if(self::isQuerySub()) {
            return self::modelInstance();
        }
        if (empty(self::$join)) {
            self::$join = " RIGHT JOIN {$table}";
        } else {
            self::$join .= " RIGHT JOIN {$table}";
        }
        if (is_callable($function)) {
            $function(self::modelInstance());
        }
        return self::modelInstance();
    }

    public static function on($field1, $compare, $field2, $operator = ''){
        if(self::isQuerySub()) {
            return self::modelInstance();
        }
        if(!empty(self::$on)){
            $operator = empty($operator) ? "AND":$operator;
            self::$operator = " {$operator} ";
        }else{
            self::$operator = " ON ";
        }
        $operator = self::$operator;
        self::$join .= "{$operator} {$field1} {$compare} {$field2}";
        return self::modelInstance();
    }

    public static function groupBy($field){
        if(self::isQuerySub()) {
            return self::modelInstance();
        }
        $field = is_array($field) ? implode(',',$field):$field;
        self::$groupBy = " GROUP BY {$field}";
        return self::modelInstance();
    }

    public static function page($page){
        if(self::isQuerySub()) {
            return self::modelInstance();
        }
        self::$page = $page;
        return self::modelInstance();
    }

    public static function limit($limit){
        if(self::isQuerySub()) {
            return self::modelInstance();
        }
        self::$limit = $limit;
        return self::modelInstance();
    }


    public static function delete(){
        $sql = self::sqlQuery(true);
        $query = self::modelInstance()->query($sql);
        if (!empty($query)) {
            return true;
        }
        return false;
    }

    public static function toSqlRaw() {
        $sql = self::sqlQuery();
        return $sql;
    }

    public static function showSqlRaw() {
        $sql = self::sqlQuery();
        log_debug($sql);
    }


    public static function clone() {
        $sql = self::sqlQuery();
        return $sql;
    }

    private static function sqlQuery($is_delete = false){
        $select = self::$select;
        $tableName = self::$tableName ? self::$tableName:static::$tableName; // ko có sẽ lấy bên model
        $join = self::$join;
        $where = self::$where;
        $whereExit = self::$whereExit;
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

        if ($is_delete) {
            $sql = "DELETE FROM {$tableName}{$where}{$whereExit}{$limit}";
            $sql = trim($sql);
            self::reset();
            return $sql;
        }

        $sql = "SELECT {$select} FROM {$tableName}{$join}
        {$where}{$whereExit}{$groupBy}{$orderBy}{$limit}{$offset}{$union}";
        $sql = trim($sql);
        self::reset();
        return $sql;
    }

    public static function create($data){
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
            $result = self::modelInstance()->query($sql, true);
            if($result){
                return self::where('id','=', $result)->first();
            }
            return false;
        }
    }

    public static function insert($data){
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
            $status = self::modelInstance()->query($sql);
            if($status){
                return true;
            }
            return false;
        }
    }

    public static function insertLastId($data){
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
            $result = self::modelInstance()->query($sql, true);
            if($result){
                return $result;
            }
            return false;
        }
    }

    public static function update($data, $fieldOrId = null){
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
            $status = self::modelInstance()->query($sql);
            if($status){
                return true;
            }
            return false;
        }
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
        self::$whereExit = '';
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
        $keys = array_keys($is_array ? $item:get_object_vars($item));
        foreach ($keys as $key) {
            $name = ucfirst($key);
            if(method_exists(self::modelInstance(), "getAttribute$name")) {
                if($is_array) {
                    $item[$key] = self::modelInstance()->{"getAttribute$name"}($item[$key]);
                } else {
                    $item->{$key} = self::modelInstance()->{"getAttribute$name"}($item->{$key});
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
        if(method_exists(self::modelInstance(), "setAttribute$key")) {
            $val = self::modelInstance()->{"setAttribute$key"}($val);
        }
        return $val;
    }

}