<?php
declare(strict_types=1);

namespace AEATech\CLISnapshotProfilerNewrelicBundle;

use AEATech\CLISnapshotProfilerEventSubscriber\OptionsFactoryInterface;
use AEATech\SnapshotProfilerNewrelic\Adapter;
use Symfony\Component\Console\Event\ConsoleCommandEvent;

class OptionsFactory implements OptionsFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function factory(ConsoleCommandEvent $event): array
    {
        $command = $event->getCommand();
        $snapshotName = $command->getName();

        return [
            Adapter::OPTION_KEY_SNAPSHOT_NAME => $snapshotName,
            Adapter::OPTION_KEY_IS_BACKGROUND_PROCESS => true,
        ];
    }
}
