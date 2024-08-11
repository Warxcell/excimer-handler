<?php

declare(strict_types=1);

namespace Warxcell\ExcimerPsrHandler;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;

use function http_build_query;
use function json_encode;

use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;
use const PHP_QUERY_RFC1738;

final readonly class SpeedscopeDataSender
{
    public function __construct(
        private string $url,
        private ClientInterface $httpClient,
        private RequestFactoryInterface $requestFactory,
        private StreamFactoryInterface $streamFactory,
    ) {
    }

    /**
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function __invoke(string $name, array $data): void
    {
        $this->httpClient->sendRequest(
            $this->requestFactory->createRequest('POST', $this->url)
                ->withBody(
                    $this->streamFactory->createStream(
                        http_build_query(
                            [
                                'name' => $name,
                                'data' => json_encode(
                                    $data,
                                    flags: JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
                                    depth: 2048
                                ),
                            ],
                            '',
                            '&',
                            PHP_QUERY_RFC1738
                        )
                    )
                )
        );
    }
}
