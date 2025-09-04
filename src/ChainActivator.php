<?php

declare(strict_types=1);

namespace Warxcell\ExcimerHandler;

use Psr\Http\Message\ServerRequestInterface;

final readonly class ChainActivator implements ProfileActivator
{
    /**
     * @param ProfileActivator $activators
     */
    public function __construct(
        private array $activators
    )
    {
    }

    public function activates(ServerRequestInterface $request): bool
    {
        foreach ($this->activators as $activator) {
            if ($activator->activates($request)) {
                return true;
            }
        }

        return false;
    }
}
