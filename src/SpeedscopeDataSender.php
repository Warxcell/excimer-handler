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
use function mb_convert_encoding;

use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

final readonly class SpeedscopeDataSender implements SpeedscopeDataSenderInterface
{
    public function __construct(
        private string $url,
        private ClientInterface $httpClient,
        private RequestFactoryInterface $requestFactory,
        private StreamFactoryInterface $streamFactory,
    ) {
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws JsonException
     */
    public function __invoke(string $name, array $data, ?array $context = null): void
    {
        $this->httpClient->sendRequest(
            $this->requestFactory->createRequest('POST', $this->url)
                ->withBody(
                    $this->streamFactory->createStream(
                        json_encode([
                            'name' => $name,
                            'data' => $this->utf8ize($data),
                            'context' => $context,
                        ], flags: JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)
                    )
                )
                ->withHeader('Content-Type', 'application/json')
        );
    }

    private function utf8ize(mixed $mixed): mixed
    {
        if (is_array($mixed)) {
            foreach ($mixed as $key => $value) {
                $mixed[$key] = $this->utf8ize($value);
            }
        } elseif (is_string($mixed)) {
            return mb_convert_encoding($mixed, 'UTF-8', 'UTF-8');
        }

        return $mixed;
    }
}
