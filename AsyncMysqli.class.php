<?php

/**
 * 使用mysqli异步执行多个SQL
 * @version 2016-02-22
 * @author redoufu
 */
namespace redoufu;

use mysqli;
use Exception;

class AsyncMysqli
{

    private $_links = array();

    private $_host;

    private $_user;

    private $_password;

    private $_database;

    private $_port;

    public function __construct($host = '127.0.0.1', $user = 'root', $password = '', $database = '', $port = '3306')
    {
        $this->_host     = $host;
        $this->_user     = $user;
        $this->_password = $password;
        $this->_database = $database;
        $this->_port     = $port;
    }
    
    /**
     * 获取数据库连接。异步执行多个sql，不能复用同一个连接
     * @param array $dbconfig
     * @throws \Exception
     * @return \mysqli
     */
    private function getConn(array $dbconfig = array())
    {
        if (! empty($dbconfig)) {
            $conn = new \mysqli($dbconfig['host'], $dbconfig['user'], $dbconfig['password'], $dbconfig['database'], $dbconfig['port']);
        } else {
            $conn = new \mysqli($this->_host, $this->_user, $this->_password, $this->_database, $this->_port);
        }
        if ($conn->connect_errno) {
            throw new \Exception('connect error:' . $conn->connect_error);
        }
        return $conn;
    }

    /**
     * 执行sql，返回结果
     * @param array $sqlconfs
     * @throws \Exception
     * @return multitype:NULL multitype:NULL
     */
    public function query(array $sqlconfs)
    {
        if (! is_array($sqlconfs) || empty($sqlconfs)) {
            throw new \Exception('param error');
        }
        //每个sql都要占用一个独立连接，不能复用同一个
        foreach ($sqlconfs as $key => $sqlconf) {
            if (! empty($sqlconf['dbconfig'])) {
                $link = self::getConn($sqlconf['dbconfig']);
            } else {
                $link = self::getConn();
            }
            $link->query($sqlconf['sql'], MYSQLI_ASYNC);
            $this->_links[] = $link;
            // 用于将每个sql的异步执行结果对应到sqlconfs数组的key上
            $hashes[spl_object_hash($link)] = $key;
        }
        
        $total = count($this->_links);
        $received = 0;
        $result = array();
        // 在接到所有连接响应之前循环调用poll
        while ($received < $total) {
            $read = $errors = $reject = $this->_links;
            // 返回就绪的连接数
            $ready_num = \mysqli::poll($read, $errors, $reject, 1);
            if (false === $ready_num) {
                $this->close();
                throw new \Exception('poll error');
            } elseif ($ready_num < 1) {
                continue;
            }
            // 可供读取数据的连接
            foreach ($read as $read_link) {
                $query_result = $read_link->reap_async_query();
                if (is_object($query_result)) {
                    // 将结果对应到传入的数组的key上
                    $result[$hashes[spl_object_hash($read_link)]] = $query_result->fetch_all(MYSQLI_ASSOC);
                } else {
                    $this->close();
                    throw new \Exception('query error:' . $read_link->error);
                }
                $received ++;
            }
            // 出错的连接
            foreach ($errors as $error_link) {
                $result[$hashes[spl_object_hash($read_link)]['key']] = array(
                    'error' => $error_link->error
                );
                // 出错的连接也视为接收完毕
                $received ++;
            }
        }
        $this->close();
        return $result;
    }
    /**
     * 每次query调用之后需要close才能做下次query
     */
    public function close(){
        if(!empty($this->_links)){
            foreach ($this->_links as $k => $v) {
                $this->_links[$k]->close();
            }
        }
        $this->_links = null;
    }
    
    public function __destruct()
    {
        $this->close();
    }
}

?>