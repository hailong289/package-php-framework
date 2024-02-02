<?php
namespace Core\Builder;

trait QueryBuilder
{
    private static $class; // tương ứng với class ben database
    protected static $tableName = '';
    protected static $where = '';
    protected static $whereExit = '';
    protected static $select = '*';
    protected static $orderBy = '';
    protected static $operator = '';
    protected static $join = '';
    protected static $on = '';
    protected static $page = '';
    protected static $limit = '';
    protected static $subQuery = '';
    protected static $union = '';
    protected static $groupBy = '';
    protected static $isSub = false;
    protected static $sqlSub = '';

    public static function table($tableName)
    {
        self::$tableName = $tableName;
        return self::modelInstance();
    }

    public static function from($tableName)
    {
        self::$tableName = $tableName;
        return self::modelInstance();
    }

    public static function subQuery($sql, $name)
    {
        if(empty($name)) throw new \RuntimeException("table subQuery is not null", 500);
        self::$tableName = "($sql) as $name";
        return self::modelInstance();
    }

    public static function union($sql)
    {
        self::$union = " UNION $sql";
        return self::modelInstance();
    }

    public static function union_all($sql)
    {
        self::$union = " UNION ALL $sql";
        return self::modelInstance();
    }

    public static function where($field, $compare = '=', $value = '')
    {
        if (is_callable($field)) {
            $field(self::modelInstance());
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
            $field(self::modelInstance());
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
        $operator = self::operator("AND");
        $value = (is_numeric($value) ? $value:"'".$value."'");
        self::$where .= "{$operator}{$field} like {$value}";
        return self::modelInstance();
    }

    public static function orWhereLike($field, $value)
    {
        $operator = self::operator("OR");
        $value = (is_numeric($value) ? $value:"'".$value."'");
        self::$where .= "{$operator}{$field} like {$value}";
        return self::modelInstance();
    }

    public static function whereIn($field, $value)
    {
        if(!is_array($value)) {
            throw new \RuntimeException("Params of {$field} is not array function whereIn", 500);
        }
        $operator = self::operator("AND");
        $value = implode(',', $value);
        self::$where .= "{$operator}{$field} in ({$value})";
        return self::modelInstance();
    }

    public static function whereNotIn($field, $value)
    {
        if(!is_array($value)) {
            throw new \RuntimeException("Params of {$field} is not array function whereNotIn", 500);
        }
        $operator =  self::operator("AND");
        $value = implode(',', $value);
        self::$where .= "{$operator}{$field} in ({$value})";
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
        $operator =  self::operator("AND");
        $value = implode(',', $value);
        self::$where .= "{$operator}{$field} BETWEEN '{$value[0]}' AND '{$value[1]}'";
        return self::modelInstance();
    }

    public static function whereRaw($sql) {
        $operator = self::operator("AND");
        self::$where .= "{$operator}{$sql}";
        return self::modelInstance();
    }

    public static function orWhereRaw($sql) {
        $operator = self::operator("OR");
        self::$where .= "{$operator}{$sql}";
        return self::modelInstance();
    }

    public static function select($field){
        $field = (is_array($field)) ? implode(", ", $field):$field;
        self::$select = $field;
        return self::modelInstance();
    }

    public static function orderBy($field, $orderBy = 'ASC'){
        self::$orderBy = " ORDER BY {$field} {$orderBy} ";
        return self::modelInstance();
    }

    public static function join($table, $function = ''){
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
        $field = is_array($field) ? implode(',',$field):$field;
        self::$groupBy = " GROUP BY {$field}";
        return self::modelInstance();
    }

    public static function page($page){
        self::$page = $page;
        return self::modelInstance();
    }

    public static function limit($limit){
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


    public static function get(){
        if(!empty(static::$query)){
            $query = static::$query->fetchAll(\PDO::FETCH_OBJ);
            static::$query = '';
            return self::modelInstance()->getCollection($query)->map(fn ($item) => self::getAttribute($item));
        }
        $sql = self::sqlQuery();
        $data = self::modelInstance()->query($sql)->fetchAll(\PDO::FETCH_OBJ);
        if (!empty($data)) {
            return self::modelInstance()->getCollection($data)->map(fn ($item) => self::getAttribute($item));
        }
        return self::modelInstance()->getCollection([]);
    }

    public static function first(){
        if(!empty(static::$query)){
            $query = static::$query->fetch(\PDO::FETCH_OBJ);
            static::$query = '';
            return self::modelInstance()->getCollection($query)->mapFirst(fn ($item) => self::getAttribute($item));
        }
        $sql = self::sqlQuery();
        $data = self::modelInstance()->query($sql)->fetch(\PDO::FETCH_OBJ);
        if (!empty($data)) {
            return self::modelInstance()->getCollection($data)->mapFirst(fn ($item) => self::getAttribute($item));
        }
        return false;
    }

    public static function getArray(){
        if(!empty(static::$query)){
            $data = static::$query->fetchAll(\PDO::FETCH_ASSOC);
            static::$query = '';
            return array_map(function ($item){
                return self::getAttribute($item, true);
            }, $data ?? []);
        }
        $sql = self::sqlQuery();
        $data = self::modelInstance()->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        if (!empty($data)) {
            return array_map(function ($item){
               return self::getAttribute($item, true);
            }, $data ?? []);
        }
        return false;
    }

    public static function firstArray(){
        if(!empty(static::$query)){
            $data = static::$query->fetch(\PDO::FETCH_ASSOC);
            static::$query = '';
            return self::getAttribute($data, true);
        }
        $sql = self::sqlQuery();
        $data = self::modelInstance()->query($sql)->fetch(\PDO::FETCH_ASSOC);
        if (!empty($data)) {
            return self::getAttribute($data, true);
        }
        return false;
    }

    public static function toSqlRaw() {
        $sql = self::sqlQuery();
        return $sql;
    }

    public static function clone() {
        $sql = self::sqlQuery();
        return $sql;
    }

    public static function findById($id) {
        $tableName = self::$tableName ? self::$tableName:static::$tableName;
        $sql = "SELECT * FROM {$tableName} WHERE id = '$id'";
        $data = self::modelInstance()->query($sql)->fetch(\PDO::FETCH_OBJ);
        if (!empty($data)) {
            return self::modelInstance()->getCollection($data)->mapFirst(fn ($item) => self::getAttribute($item));
        }
        return false;
    }

    public static function find($id) {
        $tableName = self::$tableName ? self::$tableName:static::$tableName;
        $sql = "SELECT * FROM {$tableName} WHERE id = '$id'";
        $data = self::modelInstance()->query($sql)->fetch(\PDO::FETCH_OBJ);
        if (!empty($data)) {
            return self::modelInstance()->getCollection($data)->mapFirst(fn ($item) => self::getAttribute($item));
        }
        return false;
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

    private static function operator($name) {
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