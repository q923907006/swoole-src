--TEST--
swoole_redis_coro: redis client set options
--SKIPIF--
<?php require __DIR__ . '/../include/skipif.inc'; ?>
--FILE--
<?php
require __DIR__ . '/../include/bootstrap.php';
Co::set(['socket_timeout' => -1]);
go(function () {
    $redis = new Swoole\Coroutine\Redis();
    $redis->connect(REDIS_SERVER_HOST, REDIS_SERVER_PORT);

    // read time out
    $redis->setOptions(['timeout' => 0.001]);
    $s = microtime(true);
    $ret = $redis->brpoplpush('test', 'test2', 1);
    $s = microtime(true) - $s;
    time_approximate(0.001, $s, 1);
    assert(!$ret);

    // read ok (after internal auto connect)
    $redis->setOptions(['timeout' => 1]);
    $ret = $redis->set('foo', 'bar');
    assert($ret);
    assert($redis->errCode === 0);
    assert($redis->errMsg === '');
    $redis->close();
    assert(!$redis->connected);

    // connect timeout
    $redis->setOptions(['connect_timeout' => 0.001]);
    $redis->connect('www.google.com', 80);
    assert($redis->errCode === SOCKET_ETIMEDOUT);
});
swoole_event_wait();
echo "DONE\n";
?>
--EXPECT--
DONE
