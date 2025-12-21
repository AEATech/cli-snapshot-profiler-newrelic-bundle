<?php
declare(strict_types=1);

namespace AEATech\Tests;

use AEATech\CLISnapshotProfilerEventSubscriber\EventMatcher\AllEventMatcher;
use AEATech\CLISnapshotProfilerEventSubscriber\EventMatcher\CommandEventMatcher;
use AEATech\CLISnapshotProfilerEventSubscriber\EventMatcher\EventMatcherInterface;
use AEATech\CLISnapshotProfilerNewrelicBundle\AEATechCLISnapshotProfilerNewrelicBundle;
use Nyholm\BundleTest\TestKernel;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use ReflectionException;
use ReflectionProperty;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class AEATechCLISnapshotProfilerNewrelicBundleTest extends KernelTestCase
{
    /**
     * {@inheritDoc}
     */
    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    /**
     * {@inheritDoc}
     */
    protected static function createKernel(array $options = []): KernelInterface
    {
        /**
         * @var TestKernel $kernel
         */
        $kernel = parent::createKernel($options);
        $kernel->addTestBundle(AEATechCLISnapshotProfilerNewrelicBundle::class);
        $kernel->handleOptions($options);

        return $kernel;
    }

    /**
     * @return array
     */
    public static function checkDisabledStateDataProvider(): array
    {
        return [
            'check disabled state' => [
                'config' => __DIR__ . '/Fixtures/Resources/disabled.yaml',
            ],
            'check enabled without event matcher state' => [
                'config' => __DIR__ . '/Fixtures/Resources/enabled_without_event_matcher.yaml',
            ],
        ];
    }

    /**
     * @param string $config
     *
     * @return void
     */
    #[Test]
    #[DataProvider('checkDisabledStateDataProvider')]
    public function checkDisabledState(string $config): void
    {
        self::bootKernel(['config' => static function (TestKernel $kernel) use ($config): void {
            $kernel->addTestConfig($config);
        }]);

        $container = self::getContainer();

        self::assertFalse($container->has(AEATechCLISnapshotProfilerNewrelicBundle::SERVICE_NAME_EVENT_SUBSCRIBER));
    }

    /**
     * @return void
     *
     * @throws ReflectionException
     */
    #[Test]
    public function checkEnabledWithAllEventMatcher(): void
    {
        self::bootKernel(['config' => static function (TestKernel $kernel): void {
            $kernel->addTestConfig(__DIR__ . '/Fixtures/Resources/enabled_with_all_event_matcher.yaml');
        }]);

        $container = self::getContainer();

        self::assertInstanceOf(AllEventMatcher::class, $this->getEventMatcher($container));
    }

    /**
     * @return void
     *
     * @throws ReflectionException
     */
    #[Test]
    public function checkEnabledWithCommandEventMatcher(): void
    {
        self::bootKernel(['config' => static function (TestKernel $kernel): void {
            $kernel->addTestConfig(__DIR__ . '/Fixtures/Resources/enabled_with_command_event_matcher.yaml');
        }]);

        $container = self::getContainer();

        self::assertInstanceOf(CommandEventMatcher::class, $this->getEventMatcher($container));
    }

    /**
     * @return void
     *
     * @throws ReflectionException
     */
    #[Test]
    public function checkEnabledWithAllAndCommandEventMatcher(): void
    {
        self::bootKernel(['config' => static function (TestKernel $kernel): void {
            $kernel->addTestConfig(__DIR__ . '/Fixtures/Resources/enabled_with_all_and_command_event_matcher.yaml');
        }]);

        $container = self::getContainer();

        self::assertInstanceOf(AllEventMatcher::class, $this->getEventMatcher($container));
    }

    /**
     * @param ContainerInterface $container
     *
     * @return EventMatcherInterface
     *
     * @throws ReflectionException
     */
    private function getEventMatcher(ContainerInterface $container): EventMatcherInterface
    {
        $eventSubscriber = $container->get(AEATechCLISnapshotProfilerNewrelicBundle::SERVICE_NAME_EVENT_SUBSCRIBER);
        $reflectionProperty = new ReflectionProperty($eventSubscriber, 'eventMatcher');
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty->getValue($eventSubscriber);
    }
}
