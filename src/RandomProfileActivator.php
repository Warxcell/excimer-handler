<?php

declare(strict_types=1);

namespace Warxcell\ExcimerHandler;

use Psr\Http\Message\ServerRequestInterface;

final readonly class RandomProfileActivator implements ProfileActivator
{
    public function __construct(
        private int $percent = 10
    )
    {
    }

    public function activates(ServerRequestInterface $request): bool
    {
        return mt_rand(1, 100) <= $this->percent;
    }
}
