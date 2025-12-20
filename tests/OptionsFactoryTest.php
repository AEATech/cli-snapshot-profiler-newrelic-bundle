<?php
declare(strict_types=1);

namespace AEATech\Tests;

use AEATech\CLISnapshotProfilerNewrelicBundle\OptionsFactory;
use AEATech\SnapshotProfilerNewrelic\Adapter;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OptionsFactoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private const COMMAND_NAME = 'app:command';
    
    private const OPTIONS = [
        Adapter::OPTION_KEY_SNAPSHOT_NAME => self::COMMAND_NAME,
        Adapter::OPTION_KEY_IS_BACKGROUND_PROCESS => true,
    ];
    
    private OptionsFactory $optionsFactory;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->optionsFactory = new OptionsFactory();
    }

    /**
     * @return void
     */
    #[Test]
    public function factory(): void
    {
        $command = $this->getCommand();
        $event = $this->getEvent($command);

        self::assertSame(self::OPTIONS, $this->optionsFactory->factory($event));
    }

    /**
     * @return MockInterface&Command
     */
    private function getCommand(): MockInterface&Command
    {
        $command = Mockery::mock(Command::class);
        $command->shouldReceive('getName')
            ->once()
            ->withNoArgs()
            ->andReturn(self::COMMAND_NAME);
        
        return $command;
    }

    /**
     * @param MockInterface&Command $command
     * 
     * @return ConsoleCommandEvent
     */
    private function getEvent(MockInterface&Command $command): ConsoleCommandEvent
    {
        return new ConsoleCommandEvent(
            $command,
            Mockery::mock(InputInterface::class),
            Mockery::mock(OutputInterface::class),
        );
    }
}
