<?php

declare(strict_types=1);

header("HTTP/1.0 200 OK");
header("Content-Type: text/plain");
print_r($_POST);
print_r($_FILES);
//echo empty($_ENV) ? 'LEEEER' : 'voll';
