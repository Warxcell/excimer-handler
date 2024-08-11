<?php

declare(strict_types=1);

namespace Warxcell\ExcimerHandler;

use Psr\Http\Message\ServerRequestInterface;

final readonly class DefaultProfileActivator implements ProfileActivator
{
    public function __invoke(ServerRequestInterface $request): bool
    {
        return $request->hasHeader('x-excimer-profile') || isset($request->getQueryParams()['profile']);
    }
}
