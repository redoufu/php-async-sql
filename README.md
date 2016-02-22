# php-async-sql
PHP使用mysqli相关方法异步执行多个SQL

###require
mysql驱动必须是mysqlnd

PHP版本>=5.3

###示例

```
<?php
include 'AsyncSql.class.php';

//异步sql及数据库连接配置
$sqlconfs = array(
    's1' => array(
        'dbconfig' => array(
            'host' => '127.0.0.1',
            'port' => '3306',
            'user' => 'root',
            'password' => 'liang',
            'database' => 'service'
        ),
        'sql' => 'select sleep(6) as s1'
    ),
    's2' => array(
        'dbconfig' => array(
            'host' => '127.0.0.1',
            'port' => '3306',
            'user' => 'root',
            'password' => 'liang',
            'database' => 'service'
        ),
        'sql' => 'select sleep(1) as s2'
    )
);
try {
    $re = \redoufu\AsyncSql::query($sqlconfs);
} catch (\Exception $e) {
    die($e->getMessage() . "\n");
}
var_dump($re);


```

