<?php

namespace App\Core;

abstract class Middleware
{
    abstract public function execute(Request $request, Response $response): void;
}
