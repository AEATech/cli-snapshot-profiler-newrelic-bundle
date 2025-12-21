<?php
declare(strict_types=1);

namespace AEATech\CLISnapshotProfilerNewrelicBundle;

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

    public const SERVICE_NAME_ADAPTER = self::BUNDLE_NAME_PREFIX . 'adapter';
    public const SERVICE_NAME_ALL_EVENT_MATCHER = self::EVENT_MATCHER_PREFIX . 'all';
    public const SERVICE_NAME_COMMAND_EVENT_MATCHER =  self::EVENT_MATCHER_PREFIX . 'command';
    public const SERVICE_NAME_EVENT_SUBSCRIBER = self::BUNDLE_NAME_PREFIX . 'event_subscriber';

    private const BUNDLE_NAME_PREFIX = 'aea_tech_cli_snapshot_profiler_newrelic.';
    private const EVENT_MATCHER_PREFIX = self::BUNDLE_NAME_PREFIX . 'event_matcher.';

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
        $path = $this->getPath();

        $container->import($path . '/config/services.yaml');

        $services = $container->services();

        $this->initAdapter($config, $services);
        $this->initEventMatcher($config, $services, $builder);
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

        $services->get(self::SERVICE_NAME_ADAPTER)
            ->arg('$appName', $newrelic[self::CONFIG_KEY_NEWRELIC_APP_NAME])
            ->arg('$license', $newrelic[self::CONFIG_KEY_NEWRELIC_LICENSE]);
    }

    /**
     * @param array $config
     * @param ServicesConfigurator $services
     * @param ContainerBuilder $builder
     *
     * @return void
     */
    private function initEventMatcher(array $config, ServicesConfigurator $services, ContainerBuilder $builder): void
    {
        if ($config[self::CONFIG_KEY_IS_PROFILING_ENABLED]) {
            $eventMatcherConfig = $config[self::CONFIG_KEY_EVENT_MATCHER];
            if ($eventMatcherConfig[self::CONFIG_KEY_IS_PROFILE_ALL_COMMANDS]) {
                $eventMatcherDefinition = $builder->getDefinition(self::SERVICE_NAME_ALL_EVENT_MATCHER);

                $services->get(self::SERVICE_NAME_EVENT_SUBSCRIBER)
                    ->arg('$eventMatcher', $eventMatcherDefinition);
            } elseif ($eventMatcherConfig[self::CONFIG_KEY_COMMAND][self::CONFIG_KEY_IS_ENABLED]) {
                $eventMatcher = $services->get(self::SERVICE_NAME_COMMAND_EVENT_MATCHER);
                $eventMatcher->arg(
                    '$commandNameList',
                    $eventMatcherConfig[self::CONFIG_KEY_COMMAND][self::CONFIG_KEY_NAME_LIST]
                );

                $eventMatcherDefinition = $builder->getDefinition(self::SERVICE_NAME_COMMAND_EVENT_MATCHER);

                $services->get(self::SERVICE_NAME_EVENT_SUBSCRIBER)
                    ->arg('$eventMatcher', $eventMatcherDefinition);
            } else {
                $services->remove(self::SERVICE_NAME_EVENT_SUBSCRIBER);
            }
        } else {
            $services->remove(self::SERVICE_NAME_EVENT_SUBSCRIBER);
        }
    }
}
