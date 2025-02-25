<?php
declare(strict_types=1);

namespace Warxcell\ExcimerHandler;

use Psr\Http\Message\ServerRequestInterface;

class DefaultProfileNamer implements ProfileNamer
{
    public function getName(ServerRequestInterface $request): string
    {
        return sprintf('%s %s', $request->getMethod(), $request->getUri());
    }
}