<?php

declare(strict_types=1);

namespace Warxcell\ExcimerHandler;

use Psr\Http\Message\ServerRequestInterface;

interface ProfileActivator
{
    public function __invoke(ServerRequestInterface $request): bool;
}
