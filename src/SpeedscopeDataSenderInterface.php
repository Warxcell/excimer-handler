<?php

declare(strict_types=1);

namespace Warxcell\ExcimerHandler;

use JsonException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

use function is_array;
use function is_string;
use function json_encode;

use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

interface SpeedscopeDataSenderInterface
{
    /**
     * @throws \Exception
     */
    public function __invoke(string $name, array $data, ?array $context = null): void;
}
