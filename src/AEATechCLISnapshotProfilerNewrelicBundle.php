<?php
declare(strict_types=1);

namespace AEATech\CLISnapshotProfilerNewrelicBundle;

use AEATech\CLISnapshotProfilerEventSubscriber\EventMatcher\AllEventMatcher;
use AEATech\CLISnapshotProfilerEventSubscriber\EventMatcher\CommandEventMatcher;
use AEATech\CLISnapshotProfilerEventSubscriber\EventMatcher\EventMatcherInterface;
use AEATech\CLISnapshotProfilerEventSubscriber\EventSubscriber;
use AEATech\SnapshotProfilerNewrelic\Adapter;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class AEATechCLISnapshotProfilerNewrelicBundle extends AbstractBundle
{
    public const CONFIG_KEY_IS_PROFILING_ENABLED = 'is_profiling_enabled';

    public const CONFIG_KEY_NEWRELIC = 'newrelic';
    public const CONFIG_KEY_NEWRELIC_APP_NAME = 'app_name';
    public const CONFIG_KEY_NEWRELIC_LICENSE = 'license';

    public const CONFIG_KEY_EVENT_MATCHER = 'event_matcher';

    public const CONFIG_KEY_IS_PROFILE_ALL_COMMANDS = 'is_profile_all_commands';
    public const CONFIG_KEY_IS_ENABLED = 'is_enabled';

    public const CONFIG_KEY_COMMAND = 'command';
    public const CONFIG_KEY_NAME_LIST = 'name_list';

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->booleanNode(self::CONFIG_KEY_IS_PROFILING_ENABLED)
                    ->isRequired()
                    ->info('Is profiling enabled')
                ->end()
                ->arrayNode(self::CONFIG_KEY_NEWRELIC)
                    ->isRequired()
                    ->children()
                        ->scalarNode(self::CONFIG_KEY_NEWRELIC_APP_NAME)
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->info('Newrelic app name')
                        ->end()
                        ->scalarNode(self::CONFIG_KEY_NEWRELIC_LICENSE)
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->info('Newrelic license')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode(self::CONFIG_KEY_EVENT_MATCHER)
                    ->isRequired()
                    ->children()
                        ->booleanNode(self::CONFIG_KEY_IS_PROFILE_ALL_COMMANDS)
                            ->isRequired()
                            ->info('Is profiling all commands enabled')
                        ->end()
                        ->arrayNode(self::CONFIG_KEY_COMMAND)
                            ->isRequired()
                            ->children()
                                ->booleanNode(self::CONFIG_KEY_IS_ENABLED)
                                    ->isRequired()
                                    ->info('Is profiling by command names enabled')
                                ->end()
                                ->arrayNode(self::CONFIG_KEY_NAME_LIST)
                                    ->isRequired()
                                    ->info('List of command names to profile')
                                    ->scalarPrototype()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        if ($config[self::CONFIG_KEY_IS_PROFILING_ENABLED]) {
            $path = $this->getPath();

            $container->import($path . '/config/services.yaml');

            $services = $container->services();

            $this->initAdapter($config, $services);
            $this->initEventMatcher($config, $services);
        }
    }

    /**
     * @param array $config
     * @param ServicesConfigurator $services
     *
     * @return void
     */
    private function initAdapter(array $config, ServicesConfigurator $services): void
    {
        $newrelic = $config[self::CONFIG_KEY_NEWRELIC];

        $services->get(Adapter::class)
            ->arg('$appName', $newrelic[self::CONFIG_KEY_NEWRELIC_APP_NAME])
            ->arg('$license', $newrelic[self::CONFIG_KEY_NEWRELIC_LICENSE]);
    }

    /**
     * @param array $config
     * @param ServicesConfigurator $services
     *
     * @return void
     */
    private function initEventMatcher(array $config, ServicesConfigurator $services): void
    {
        $eventMatcherConfig = $config[self::CONFIG_KEY_EVENT_MATCHER];
        if ($eventMatcherConfig[self::CONFIG_KEY_IS_PROFILE_ALL_COMMANDS]) {
            $services->alias(EventMatcherInterface::class, AllEventMatcher::class);
        } elseif ($eventMatcherConfig[self::CONFIG_KEY_COMMAND][self::CONFIG_KEY_IS_ENABLED]) {
            $eventMatcher = $services->get(CommandEventMatcher::class);
            $eventMatcher->arg(
                '$commandNameList',
                $eventMatcherConfig[self::CONFIG_KEY_COMMAND][self::CONFIG_KEY_NAME_LIST]
            );
            $services->alias(EventMatcherInterface::class, CommandEventMatcher::class);
        } else {
            $services->remove(EventSubscriber::class);
        }
    }
}
