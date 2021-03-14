<?php
declare(strict_types = 1);

require_once __DIR__.'\ss_api.php';

use ss\api\ss_api;

function run():array {
	$ss = new ss_api('EXAMPLE', '127.0.0.1', 49776);
	return $ss->game_event('TEST', ['value' => 75, 'frame' => ["<arbitrary key>" => 'value']]);
}

var_dump(run());