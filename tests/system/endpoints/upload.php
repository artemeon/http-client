<?php

declare(strict_types=1);

header("HTTP/1.0 200 OK");
header("Content-Type: text/plain");
echo '<h1>REQUEST</h1>';
print_r($_REQUEST);
echo '<h1>FILES</h1>';
print_r($_FILES);
echo '<h1>Raw</h1>';
echo file_get_contents('php://input');
