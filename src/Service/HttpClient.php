<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Service;

use Artemeon\HttpClient\Model\Request;

interface HttpClient
{
    public function send(Request $request);
}
