<?php
declare(strict_types=1);

namespace Warxcell\ExcimerHandler;

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface ContextProvider
{
    public function getCommandContext(Command $command, InputInterface $input, OutputInterface $output, int $exitCode): ?array;

    public function getRequestContext(ServerRequestInterface $request): ?array;
}