<?php
$url = 'http://2009.aiitec.net/jihong/public';
$task_one = $url.'/admina/goodsWeekStatistics';
echo file_get_contents($task_one);