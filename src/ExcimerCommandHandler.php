<?php

declare(strict_types=1);

namespace Warxcell\ExcimerPsrHandler;

use ExcimerProfiler;
use Psr\Http\Client\ClientExceptionInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use const EXCIMER_REAL;

final class ExcimerCommandHandler implements EventSubscriberInterface
{
    private ?ExcimerProfiler $profiler = null;

    public function __construct(
        private readonly SpeedscopeDataSender $speedscopeDataSender,
        private readonly bool $enabled
    ) {
    }

    public static function getSubscribedEvents()
    {
        return [
            ConsoleEvents::COMMAND => 'onCommand',
            ConsoleEvents::TERMINATE => 'onTerminate',
        ];
    }

    public function onCommand()
    {
        if (!$this->enabled) {
            return;
        }
        $this->profiler = new ExcimerProfiler();
        $this->profiler->setPeriod(0.001); // 1ms
        $this->profiler->setEventType(EXCIMER_REAL);
        $this->profiler->start();
    }

    public function onTerminate(ConsoleTerminateEvent $event)
    {
        if (!$this->profiler) {
            return;
        }

        $this->profiler->stop();
        $data = $this->profiler->getLog()->getSpeedscopeData();

        try {
            ($this->speedscopeDataSender)(name: $event->getCommand()->getName() ?? 'Unknown command', data: $data);
        } catch (ClientExceptionInterface|JsonException $exception) {
            $this->logger->error(
                $exception->getMessage(),
                [
                    'exception' => $exception,
                ]
            );
        } finally {
            $this->profiler = null;
        }
    }
}
