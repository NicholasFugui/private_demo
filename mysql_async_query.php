<?php
/**
* mysqli+mysqlnd.
* mysqlnd_async_query		发送查询请求
* mysqlnd_reap_async_query	获取查询结果
*/
$host = '127.0.0.1';
$user = 'root';
$password = '';
$database = 'test';

/**
 * 期望得到额结果
 * array(
 *  1 => int,
 *  2 => int,
 *  3 => int
 * )
 */
$result = array(1=>0, 2=>0, 3=>0);

//异步方式[并发请求]
$time_start = microtime(true);
$links = array();

foreach ($result as $key => $value) {
	$obj = new mysqli($host,$user,$password,$database);
	$links[spl_object_hash($obj)] = array('value'=>$key,'link'=>$obj);
}
$done = 0;
$total = count($links);
foreach ($links as $value) {
    $value['link']->query("SELECT COUNT(*) AS `total` FROM `demo` WHERE `value`={$value['value']}", MYSQLI_ASYNC);
}
do {
  
    $tmp = array();
    foreach ($links as $value) {
        $tmp[] = $value['link'];
    }
   $read = $errors = $reject = $tmp;
    $re = mysqli_poll($read, $errors, $reject, 1);
    if (false === $re) {
        die('mysqli_poll failed');
    } elseif ($re < 1) {
        continue;
    }
     foreach ($read as $link) {
        $sql_result = $link->reap_async_query();
        if (is_object($sql_result)) {
            $sql_result_array = $sql_result->fetch_array(MYSQLI_ASSOC);//只有一行
            $sql_result->free();
            $hash = spl_object_hash($link);
            $key_in_result = $links[$hash]['value'];
            $result[$key_in_result] = $sql_result_array['total'];
        } else {
            echo $link->error, "\n";
        }
        $done++;
    }
  
    foreach ($errors as $link) {
        echo $link->error, "1\n";
        $done++;
    }
     foreach ($reject as $link) {
        printf("server is busy, client was rejected.\n", $link->connect_error, $link->error);
        //这个地方别再$done++了。
    }
} while ($done<$total);
var_dump($result);
echo "ASYNC_QUERY_TIME:", microtime(true)-$time_start, "\n";
  
$link = end($links);
$link = $link['link'];
echo "\n";

/**
 *mysql数据库对于每个查询请求都是单独启动一个线程进行处理。如果mysql服务器启动线程过多，必然会造成线程切换引起系统负载过高。 *如果在mysql数据库负载不高的情况下，使用异步查询还是不错的选择。
**/
?>