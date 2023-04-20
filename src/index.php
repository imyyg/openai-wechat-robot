<?php
error_reporting(1 | 2 | 4);

require_once "Main.php";
require_once './common/Log.php';

$data = file_get_contents('php://input');

\common\Log::save($data, 'request');

$main = new Main();

echo $main->reply($data);

exit();