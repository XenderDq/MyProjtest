<?php
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);
$path = trim($path, '/');

?>
<div class = "non_find">Страница:  <?=$path?> не найдена или не доступна</div>
