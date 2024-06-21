<?php
namespace System\Core;

class Model {

    private static function DB()
    {
       return new Database();
    }

    private static function getTable()
    {
        $call_class = get_called_class();
        $list_vars = get_class_vars($call_class);
        $variable = str_replace('App\\Models\\','', $call_class);
        $tableName = strtolower($variable);
        return $list_vars['tableName'] ?? $tableName;
    }

    public static function instance()
    {
        $table = self::getTable();
        return self::DB()->table($table);
    }

    public static function from($tableName) {
        return self::DB()->from($tableName);
    }

    public static function subQuery($sql, $name) {
        $table = self::getTable();
        return self::DB()->table($table)->subQuery($sql, $name);
    }

    public static function union($sql) {
        $table = self::getTable();
        return self::DB()->table($table)->union($sql);
    }

    public static function union_all($sql) {
        $table = self::getTable();
        return self::DB()->table($table)->union_all($sql);
    }

    public static function where($field, $compare = '=', $value = null) {
        $table = self::getTable();
        return self::DB()->table($table)->where($field, $compare, $value);
    }

    public static function orWhere($field, $compare = '=', $value = null) {
        $table = self::getTable();
        return self::DB()->table($table)->orWhere($field, $compare, $value);
    }

    public static function whereLike($field, $value) {
        $table = self::getTable();
        return self::DB()->table($table)->whereLike($field, $value);
    }

    public static function orWhereLike($field, $value) {
        $table = self::getTable();
        return self::DB()->table($table)->orWhereLike($field, $value);
    }

    public static function whereIn($field, array $value) {
        $table = self::getTable();
        return self::DB()->table($table)->whereIn($field, $value);
    }

    public static function orWhereIn($field, array $value) {
        $table = self::getTable();
        return self::DB()->table($table)->orWhereIn($field, $value);
    }

    public static function whereNotIn($field, array $value) {
        $table = self::getTable();
        return self::DB()->table($table)->whereNotIn($field, $value);
    }

    public static function orWhereNotIn($field, array $value) {
        $table = self::getTable();
        return self::DB()->table($table)->orWhereNotIn($field, $value);
    }

    public static function whereBetween($field, array $value) {
        $table = self::getTable();
        return self::DB()->table($table)->whereBetween($field, $value);
    }

    public static function whereRaw($sql) {
        $table = self::getTable();
        return self::DB()->table($table)->whereRaw($sql);
    }

    public static function orWhereRaw($sql) {
        $table = self::getTable();
        return self::DB()->table($table)->orWhereRaw($sql);
    }

    public static function select($field) {
        $table = self::getTable();
        return self::DB()->table($table)->select($field);
    }

    public static function orderBy($field, $orderBy = 'ASC') {
        $table = self::getTable();
        return self::DB()->table($table)->orderBy($field, $orderBy);
    }
    public static function join($table, $function = '') {
        $table = self::getTable();
        return self::DB()->table($table)->orWhereRaw($table, $function);
    }

    public static function leftJoin($table, $function) {
        $table = self::getTable();
        return self::DB()->table($table)->leftJoin($table, $function);
    }

    public static function rightJoin($table, $function) {
        $table = self::getTable();
        return self::DB()->table($table)->rightJoin($table, $function);
    }

    public static function on($field1, $compare, $field2, $operator = '') {
        $table = self::getTable();
        return self::DB()->table($table)->on($field1, $compare, $field2, $operator);
    }

    public static function groupBy($field) {
        $table = self::getTable();
        return self::DB()->table($table)->groupBy($field);
    }

    public static function page($page) {
        $table = self::getTable();
        return self::DB()->table($table)->page($page);
    }

    public static function limit($limit) {
        $table = self::getTable();
        return self::DB()->table($table)->limit($limit);
    }

    public static function delete() {
        $table = self::getTable();
        return self::DB()->table($table)->delete();
    }

    public static function toSqlRaw() {
        $table = self::getTable();
        return self::DB()->table($table)->toSqlRaw();
    }

    public static function showSqlRaw() {
        $table = self::getTable();
        return self::DB()->table($table)->showSqlRaw();
    }

    public static function clone() {
        $table = self::getTable();
        return self::DB()->table($table)->clone();
    }

    public static function create($data) {
        $table = self::getTable();
        return self::DB()->table($table)->create($data);
    }

    public static function insert($data) {
        $table = self::getTable();
        return self::DB()->table($table)->insert($data);
    }

    public static function insertLastId($data) {
        $table = self::getTable();
        return self::DB()->table($table)->insertLastId($limit);
    }

    public static function update($data, $fieldOrId = null) {
        $table = self::getTable();
        return self::DB()->table($table)->update($data, $fieldOrId);
    }

    public static function updateOrInsert($data, $fieldOrId) {
        $table = self::getTable();
        return self::DB()->table($table)->updateOrInsert($data, $fieldOrId);
    }

    public static function get() {
        $table = self::getTable();
        return self::DB()->table($table)->get();
    }

    public static function getArray() {
        $table = self::getTable();
        return self::DB()->table($table)->getArray();
    }

    public static function first() {
        $table = self::getTable();
        return self::DB()->table($table)->first();
    }

    public static function firstArray() {
        $table = self::getTable();
        return self::DB()->table($table)->firstArray();
    }

    public static function findById($id) {
        $table = self::getTable();
        return self::DB()->table($table)->findById($id);
    }

    public static function find($id) {
        $table = self::getTable();
        return self::DB()->table($table)->find($id);
    }

    public static function count($key = '*', $as = 'count') {
        $table = self::getTable();
        return self::DB()->table($table)->count($key, $as);
    }

    public static function sum($key = '*', $as = '') {
        $table = self::getTable();
        return self::DB()->table($table)->sum($key, $as);
    }

    public static function with($name) {
        $table = self::getTable();
        return self::DB()->table($table)->with($name);
    }
}