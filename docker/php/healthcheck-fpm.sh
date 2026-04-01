#!/bin/sh
set -e
php -r '$c = @stream_socket_client("tcp://127.0.0.1:9000", $errno, $errstr, 1);
if ($c) { fclose($c); exit(0); }
exit(1);'
