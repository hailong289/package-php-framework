<?php

namespace Hola\Database;

class Model {
    private static QueryBuilder $builder;

    private static function build() {
        self::$builder = QueryBuilder::conn();
        $nameModel = get_called_class();
        $varModel = get_class_vars($nameModel);
        self::$builder->setModel($nameModel, $varModel);
        self::$builder->from(self::table($varModel, $nameModel));
        return self::$builder;
    }

    private static function table($varModel, $nameModel) {
        $variable = str_replace('App\\Models\\','', $nameModel);
        $tableName = strtolower($variable);
        return $varModel['table'] ?? $tableName;
    }
    
    public function init() {
        return self::build();
    }

    public static function enableQueryLog() {
        return self::build()->enableQueryLog();
    }

    public static function getQueryLog() {
        return self::build()->getQueryLog();
    }

    public static function beginTransaction()
    {
        return self::build()->beginTransaction();
    }

    public static function commit()
    {
        return self::build()->commit();
    }

    public static function rollBack()
    {
        return self::build()->rollBack();
    }

    public static function connection($conn = null)
    {
        return self::build()->connection($conn);
    }

    public static function select($columns = ['*'])
    {
        return self::build()->select($columns);
    }

    public static function from($table, $as = null)
    {
        return self::build()->from($table, $as);
    }

    public static function join($table, $first, $operator = null, $second = null, $type = 'INNER')
    {
        return self::build()->join($table, $first, $operator, $second, $type);
    }

    public static function leftJoin($table, $first, $operator = null, $second = null)
    {
        return self::build()->leftJoin($table, $first, $operator, $second);
    }

    public static function rightJoin($table, $first, $operator = null, $second = null)
    {
        return self::build()->rightJoin($table, $first, $operator, $second);
    }

    public static function crossJoin($table, $first, $operator = null, $second = null)
    {
        return self::build()->crossJoin($table, $first, $operator, $second);
    }

    public static function where($column, $operator = null, $value = null)
    {
        return self::build()->where($column, $operator, $value);
    }

    public static function orWhere($column, $operator = null, $value = null)
    {
        return self::build()->orWhere($column, $operator, $value);
    }

    public static function whereLike($column, $value)
    {
        return self::build()->whereLike($column, $value);
    }

    public static function orWhereLike($column, $value)
    {
        return self::build()->orWhereLike($column, $value);
    }

    public static function whereBetween($column, array $value)
    {
        return self::build()->whereBetween($column, $value);
    }

    public static function whereIn($column, array $value)
    {
        return self::build()->whereIn($column, $value);
    }

    public static function whereNotIn($column, array $value)
    {
        return self::build()->whereNotIn($column, $value);
    }

    public static function whereRaw($sql)
    {
        return self::build()->whereRaw($sql);
    }

    public static function orWhereRaw($sql)
    {
        return self::build()->orWhereRaw($sql);
    }

    public static function groupBy($columns)
    {
        return self::build()->groupBy($columns);
    }

    public static function having($column, $operator = null, $value = null, $boolean = 'and')
    {
        return self::build()->having($column, $operator, $value, $boolean);
    }

    public static function orderBy($column, $direction = 'asc')
    {
        return self::build()->orderBy($column, $direction);
    }

    public static function limit($value)
    {
        return self::build()->limit($value);
    }

    public static function offset()
    {
        return self::build()->offset($value);
    }

    public static function union($query, $all = false)
    {
        return self::build()->union($query, $all);
    }

    public static function with($name, $useN1Query = false)
    {
        return self::build()->with($name, $useN1Query);
    }

    public static function toSql($type = 'SELECT')
    {
        return self::build()->toSql($type);
    }

    public static function get()
    {
        return self::build()->get();
    }

    public static function first()
    {
        return self::build()->first();
    }

    public static function insert($data)
    {
        return self::build()->insert($data);
    }

    public static function insertLastId($data)
    {
        return self::build()->insertLastId($data);
    }

    public static function update($data, $id = null)
    {
        return self::build()->update($data, $id);
    }

    public static function delete($data, $id = null)
    {
        return self::build()->delete($data, $id);
    }
    
    public function hasOne($related, $foreign_key, $key = 'id') {
        $parent_function = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'];
        return self::build()->relations($related, null, $parent_function, $foreign_key, null, $key, 'HAS_ONE');
    }

    public function hasMany($related, $foreign_key, $key = 'id') {
        $parent_function = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'];
        return self::build()->relations($related, null, $parent_function, $foreign_key, null, $key, 'HAS_MANY');
    }

    public function belongsTo($related, $foreign_key, $key = 'id') {
        $parent_function = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'];
        return self::build()->relations($related, null, $parent_function, $foreign_key, null, $key, 'BELONG_TO');
    }

    public function belongsToMany($related, $table_3rd, $foreign_key, $foreign_key2, $key = 'id') {
        $parent_function = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'];
        return self::build()->relations($related, $table_3rd, $parent_function, $foreign_key, $foreign_key2, $key, 'BELONGS_TO_MANY');
    }

    public function manyToMany($related, $table_3rd, $foreign_key, $foreign_key2, $key = 'id') {
        $parent_function = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'];
        return self::build()->relations($related, $table_3rd, $parent_function, $foreign_key, $foreign_key2, $key, 'MANY_TO_MANY');
    }
}