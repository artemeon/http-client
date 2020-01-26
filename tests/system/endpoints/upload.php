<?php

declare(strict_types=1);

header("HTTP/1.0 200 OK");
header("Content-Type: text/plain");
echo '<h1>TARGET SERVER:</h1>';
echo '<h2>REQUEST</h2>';
print_r($_REQUEST);
echo '<h2>FILES</h2>';
print_r($_FILES);
echo '<h2>Raw</h2>';
echo file_get_contents('php://input');
