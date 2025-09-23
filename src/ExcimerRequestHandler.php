<?php

declare(strict_types=1);

namespace Warxcell\ExcimerHandler;

use ExcimerProfiler;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

use const EXCIMER_REAL;

// https://www.speedscope.app/
final readonly class ExcimerRequestHandler implements RequestHandlerInterface
{
    public function __construct(
        private RequestHandlerInterface $handler,
        private LoggerInterface $logger,
        private SpeedscopeDataSender $speedscopeDataSender,
        private ProfileActivator $profileActivator = new DefaultProfileActivator(),
        private ProfileNamer $profileNamer = new DefaultProfileNamer(),
        private ?ContextProvider $contextProvider = null,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $shouldProfile = $this->profileActivator->activates($request);

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
                ($this->speedscopeDataSender)(
                    name: $this->profileNamer->getName($request),
                    data: $data,
                    context: $this->contextProvider?->getRequestContext($request),
                );
            } catch (ClientExceptionInterface|JsonException $exception) {
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
