<?php
namespace Hola\Core;

class Model {

    private static function init($env = null, $connection = null, $not_set_tb = false)
    {
        $call_class = get_called_class();
        $var = get_class_vars($call_class);
        $db = new Database($env, $connection);
        $db->setModel($call_class, $var);
        // set table
        $variable = str_replace('App\\Models\\','', $call_class);
        $tableName = strtolower($variable);
        $table = $var['tableName'] ?? $tableName;
        if ($not_set_tb === false) {
            $db->table($table);
        }
        return $db;
    }

    public static function instance()
    {
        return self::init();
    }

    public static function connection($env = 'default', $connection = 'mysql')
    {
        return self::init($env, $connection);
    }

    public static function from($tableName) {
        return self::init(null, null, true)->from($tableName);
    }

    public static function subQuery($sql, $name) {
        return self::init()->subQuery($sql, $name);
    }

    public static function union($sql) {
        return self::init()->union($sql);
    }

    public static function union_all($sql) {
        return self::init()->union_all($sql);
    }

    public static function where($field, $compare = '=', $value = null) {
        return self::init()->where($field, $compare, $value);
    }

    public static function orWhere($field, $compare = '=', $value = null) {
        return self::init()->orWhere($field, $compare, $value);
    }

    public static function whereLike($field, $value) {
        return self::init()->whereLike($field, $value);
    }

    public static function orWhereLike($field, $value) {
        return self::init()->orWhereLike($field, $value);
    }

    public static function whereIn($field, array $value) {
        return self::init()->whereIn($field, $value);
    }

    public static function orWhereIn($field, array $value) {
        return self::init()->orWhereIn($field, $value);
    }

    public static function whereNotIn($field, array $value) {
        return self::init()->whereNotIn($field, $value);
    }

    public static function orWhereNotIn($field, array $value) {
        return self::init()->orWhereNotIn($field, $value);
    }

    public static function whereBetween($field, array $value) {
        return self::init()->whereBetween($field, $value);
    }

    public static function whereRaw($sql) {
        return self::init()->whereRaw($sql);
    }

    public static function orWhereRaw($sql) {
        return self::init()->orWhereRaw($sql);
    }

    public static function select($field) {
        return self::init()->select($field);
    }

    public static function orderBy($field, $orderBy = 'ASC') {
        return self::init()->orderBy($field, $orderBy);
    }
    public static function join($table, $function = null) {
        return self::init()->join($table, $function);
    }

    public static function leftJoin($table, $function = null) {
        return self::init()->leftJoin($table, $function);
    }

    public static function rightJoin($table, $function = null) {
        return self::init()->rightJoin($table, $function);
    }

    public static function on($field1, $compare, $field2, $operator = '') {
        return self::init()->on($field1, $compare, $field2, $operator);
    }

    public static function groupBy($field) {
        return self::init()->groupBy($field);
    }

    public static function page($page) {
        return self::init()->page($page);
    }

    public static function limit($limit) {
        return self::init()->limit($limit);
    }

    public static function delete() {
        return self::init()->delete();
    }

    public static function toSqlRaw() {
        return self::init()->toSqlRaw();
    }

    public static function showSqlRaw() {
        return self::init()->showSqlRaw();
    }

    public static function clone() {
        return self::init()->clone();
    }

    public static function create($data) {
        return self::init()->create($data);
    }

    public static function insert($data) {
        return self::init()->insert($data);
    }

    public static function insertLastId($data) {
        return self::init()->insertLastId($data);
    }

    public static function update($data, $fieldOrId = null) {
        return self::init()->update($data, $fieldOrId);
    }

    public static function updateOrInsert($data, $fieldOrId) {
        return self::init()->updateOrInsert($data, $fieldOrId);
    }

    public static function get() {
        return self::init()->get();
    }

    public static function getArray() {
        return self::init()->getArray();
    }

    public static function first() {
        return self::init()->first();
    }

    public static function firstArray() {
        return self::init()->firstArray();
    }

    public static function findById($id) {
        return self::init()->findById($id);
    }

    public static function find($id) {
        return self::init()->find($id);
    }

    public static function count($key = '*', $as = 'count') {
        return self::init()->count($key, $as);
    }

    public static function sum($key = '*', $as = '') {
        return self::init()->sum($key, $as);
    }

    public static function with($name, $useN1Query = false) {
        return self::init()->with($name, $useN1Query);
    }

    public static function query($sql) {
        return self::init()->query($sql);
    }

    public function save()
    {
        $data = convert_to_array($this);
        $id = 0;
        if (isset($data['id'])) {
            $id = $data['id'];
            unset($data['id']);
        }
        return self::init()->updateOrInsert($data, $id);
    }
}