# php-async-sql
PHP使用mysqli相关方法异步执行多个SQL

###require
mysql驱动必须是mysqlnd

PHP版本>=5.3

###示例

```
<?php
include 'AsyncMysqli.class.php';

// 同一个database情况
$dbconf = array(
    'host'     => '127.0.0.1',
    'user'     => 'root',
    'password' => 'liang',
    'database' => 'service',
    'port'     => '3306'
);
$sqlconfs = array(
    's1' => array(
        'sql' => 'select sleep(1) as s1'
    ),
    's2' => array(
        'sql' => 'select sleep(1) as s2'
    )
);
try {
    $async = new \redoufu\AsyncMysqli($dbconf['host'], $dbconf['user'], $dbconf['password'], $dbconf['database'], $dbconf['port']);
    $re1 = $async->query($sqlconfs);
    $re2 = $async->query($sqlconfs);
    var_dump($re1, $re2);
} catch (\Exception $e) {
    die($e->getMessage() . "\n");
}
die;
// 不同database调用方法：
$sqlconfs = array(
    's1' => array(
        'dbconfig'     => array(
            'host'     => '127.0.0.1',
            'user'     => 'root',
            'password' => 'liang',
            'database' => 'service',
            'port'     => '3306'
        ),
        'sql' => 'select sleep(1) as s1'
    ),
    's2' => array(
        'dbconfig'     => array(
            'host'     => '127.0.0.1',
            'user'     => 'root',
            'password' => 'liang',
            'database' => 'test',
            'port'     => '3306'
        ),
        'sql' => 'select sleep(1) as s2'
    )
);
try {
    $async = new \redoufu\AsyncMysqli();
    $re = $async->query($sqlconfs);
    $async->close();
    var_dump($re);
} catch (\Exception $e) {
    die($e->getMessage() . "\n");
}

```

