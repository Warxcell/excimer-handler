<?php

declare(strict_types=1);

namespace Warxcell\ExcimerPsrHandler;

use ExcimerProfiler;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

use function http_build_query;
use function json_encode;

use const EXCIMER_REAL;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

// https://www.speedscope.app/
final readonly class ExcimerRequestHandler implements RequestHandlerInterface
{
    public function __construct(
        private RequestHandlerInterface $handler,
        private string $url,
        private LoggerInterface $logger,
        private ClientInterface $httpClient,
        private RequestFactoryInterface $requestFactory,
        private StreamFactoryInterface $streamFactory,
        private ProfileActivator $profileActivator = new DefaultProfileActivator(),
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $shouldProfile = ($this->profileActivator)($request);

        if (!$shouldProfile) {
            return $this->handler->handle($request);
        }

        $excimer = new ExcimerProfiler();
        $excimer->setPeriod(0.001); // 1ms
        $excimer->setEventType(EXCIMER_REAL);
        $excimer->start();

        try {
            return $this->handler->handle($request);
        } finally {
            $excimer->stop();
            $data = $excimer->getLog()->getSpeedscopeData();

            try {
                $this->httpClient->sendRequest(
                    $this->requestFactory->createRequest('POST', $this->url)
                        ->withBody(
                            $this->streamFactory->createStream(
                                http_build_query(
                                    [
                                        'name' => (string)$request->getUri(),
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
            } catch (ClientExceptionInterface $exception) {
                $this->logger->error(
                    $exception->getMessage(),
                    [
                        'exception' => $exception,
                    ]
                );
            }
        }
    }
}
