<?php
namespace Hola\Connection\Interfaces;
interface IConnections {
    public function connect();
    public function isConnect();
    public function reConnect();
    public function getConnection();
}