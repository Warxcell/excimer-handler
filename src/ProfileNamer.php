<?php
declare(strict_types=1);

namespace Warxcell\ExcimerHandler;

use Psr\Http\Message\ServerRequestInterface;

interface ProfileNamer
{
    public function getName(ServerRequestInterface $request): string;
}