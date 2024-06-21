<?php
namespace System\Traits;

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

    private function startQuerySub(){ 
        $this->startSub = true; 
    }
    
    private function endQuerySub(){ 
        $this->startSub = false; 
        $this->resetSub(); 
    }
    
    private function isQuerySub(){ 
        return $this->startSub; 
    }

    public function table($tableName)
    {
        if($this->isQuerySub()) {
            $this->tableNameSub = $tableName;
            return $this;
        }
        $this->tableName = $tableName;
        return $this;
    }

    public function from($tableName)
    {
        if($this->isQuerySub()) {
            $this->tableNameSub = $tableName;
            return $this;
        }
        $this->tableName = $tableName;
        return $this;
    }

    public function subQuery($sql, $name)
    {
        if($this->isQuerySub()) {
            return $this;
        }
        if(empty($name)) throw new \RuntimeException("table subQuery is not null", 500);
        $this->tableName = "($sql) as $name";
        return $this;
    }

    public function union($sql)
    {
        if($this->isQuerySub()) {
            return $this;
        }
        $this->union = " UNION $sql";
        return $this;
    }

    public function union_all($sql)
    {
        if($this->isQuerySub()) {
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
        if($this->isQuerySub()) {
            $operator = $this->operator('AND', true);
            if(is_null($value)) {
                $value = $compare;
                $compare = '=';
            }
            $value = (is_numeric($value) ? $value:"'".$value."'");
            $this->whereSub .= "{$operator}{$field} {$compare} {$value}";
            return $this;
        }
        $operator = $this->operator('AND');
        if(is_null($value)) {
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
        if($this->isQuerySub()) {
            $operator = $this->operator('OR', true);
            if(is_null($value)) {
                $value = $compare;
                $compare = '=';
            }
            $value = (is_numeric($value) ? $value:"'".$value."'");
            $this->whereSub .= "{$operator}{$field} {$compare} {$value}";
            return $this;
        }
        $operator = $this->operator("OR");
        if(is_null($value)) {
            $value = $compare;
            $compare = '=';
        }
        $value = (is_numeric($value) ? $value:"'".$value."'");
        $this->where .= "{$operator}{$field} {$compare} {$value}";
        return $this;
    }

    public function whereLike($field, $value)
    {
        if($this->isQuerySub()) {
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
        if($this->isQuerySub()) {
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
        if(!is_array($value)) {
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
        if(!is_array($value)) {
            throw new \RuntimeException("Params of {$field} is not array function whereIn", 500);
        }
        if($this->isQuerySub()) {
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
        if(!is_array($value)) {
            throw new \RuntimeException("Params of {$field} is not array function whereNotIn", 500);
        }
        if($this->isQuerySub()) {
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
        if(!is_array($value)) {
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

    public function whereBetween($field, $value)
    {
        if(!is_array($value)) {
            throw new \RuntimeException("Params of {$field} is not array function whereBetween", 500);
        }
        if(count($value) > 2) {
            throw new \RuntimeException("The value in the array is more than 2 function whereBetween", 500);
        }
        if($this->isQuerySub()) {
            $operator = $this->operator('OR', true);
            $this->whereSub .= "{$operator}{$field} BETWEEN '{$value[0]}' AND '{$value[1]}'";
            return $this;
        }
        $operator = $this->operator("AND");
        $this->where .= "{$operator}{$field} BETWEEN '{$value[0]}' AND '{$value[1]}'";
        return $this;
    }

    public function whereRaw($sql) {
        if($this->isQuerySub()) {
            $operator = $this->operator('AND', true);
            $this->whereSub .= "{$operator}{$sql}";
            return $this;
        }
        $operator = $this->operator("AND");
        $this->where .= "{$operator}{$sql}";
        return $this;
    }

    public function orWhereRaw($sql) {
        if($this->isQuerySub()) {
            $operator = $this->operator('OR', true);
            $this->whereSub .= "{$operator}{$sql}";
            return $this;
        }
        $operator = $this->operator("OR");
        $this->where .= "{$operator}{$sql}";
        return $this;
    }

    public function select($field){
        if($this->isQuerySub()) {
            $field = (is_array($field)) ? implode(", ", $field):$field;
            $this->selectSub = $field;
            return $this;
        }
        $field = (is_array($field)) ? implode(", ", $field):$field;
        $this->select = $field;
        return $this;
    }

    public function orderBy($field, $orderBy = 'ASC'){
        if($this->isQuerySub()) {
            return $this;
        }
        $this->orderBy = " ORDER BY {$field} {$orderBy} ";
        return $this;
    }

    public function join($table, $function = ''){
        if($this->isQuerySub()) {
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

    public function leftJoin($table, $function = ''){
        if($this->isQuerySub()) {
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

    public function rightJoin($table, $function = ''){
        if($this->isQuerySub()) {
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

    public function on($field1, $compare, $field2, $operator = ''){
        if($this->isQuerySub()) {
            return $this;
        }
        if(!empty($this->on)){
            $operator = empty($operator) ? "AND":$operator;
            $this->operator = " {$operator} ";
        }else{
            $this->operator = " ON ";
        }
        $operator = $this->operator;
        $this->join .= "{$operator} {$field1} {$compare} {$field2}";
        return $this;
    }

    public function groupBy($field){
        if($this->isQuerySub()) {
            return $this;
        }
        $field = is_array($field) ? implode(',',$field):$field;
        $this->groupBy = " GROUP BY {$field}";
        return $this;
    }

    public function page($page){
        if($this->isQuerySub()) {
            return $this;
        }
        $this->page = $page;
        return $this;
    }

    public function limit($limit){
        if($this->isQuerySub()) {
            return $this;
        }
        $this->limit = $limit;
        return $this;
    }


    public function delete(){
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
            }else{
                $fieldTable = implode(',', $fieldTable);
            }
            $select = $fieldTable;
        }

        if(!empty($query)) { // use count, sum
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

    public function create($data){
        $tableName = $this->tableName;
        $fieldTable = $this->field ?? [];
        $fieldTableNone = [];
        if(!empty($data)){
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
            if(count($fieldTableNone) > 0){
                $class = get_class($this);
                $fieldTableNone = implode(',', $fieldTableNone);
                throw new \RuntimeException("Missing $fieldTableNone in fieldTable class $class", 500);
            }
            $field = rtrim($field, ',');
            $value = rtrim($value, ',');
            $sql = "INSERT INTO $tableName($field) VALUES ($value)";
            $result = $this->query($sql, true);
            $this->reset();
            if($result){
                return $this->findById($result)->value();
            }
            return false;
        }
    }

    public function insert($data){
        $tableName = $this->tableName;
        if(!empty($data)){
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
            if($status){
                return true;
            }
            return false;
        }
    }

    public function insertLastId($data){
        $tableName = $this->tableName;
        if(!empty($data)){
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
            if($result){
                return $result;
            }
            return false;
        }
    }

    public function update($data, $fieldOrId = null){
        $tableName = $this->tableName;
        if(!empty($data)){
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
            if(empty($fieldOrId)) {
                $where = $this->where;
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
            $this->reset();
            if($status){
                return true;
            }
            return false;
        }
    }

    public function updateOrInsert($data, $fieldOrId)
    {
        $tableName = $this->tableName;
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
        if (isset(static::$data_relation)) static::$data_relation = [];
    }

    private function operator($name, $isSub = false) {
        if($isSub) {
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

    private function getAttribute($item, $is_array = false)
    {
        $instance = !empty($this->getModel()) ? $this->getModel():$this;
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
    private function setAttribute($key, $val){
        $instance = !empty($this->getModel()) ? $this->getModel():$this;
        if(method_exists($instance, "setAttribute$key")) {
            $val = $instance->{"setAttribute$key"}($val);
        }
        return $val;
    }

}