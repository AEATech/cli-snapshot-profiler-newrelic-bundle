# AEATech - CLI snapshot profiler newrelic bundle

[![Code Coverage](.build/coverage_badge.svg)](.build/clover.xml)

The package contains symfony bundle to profile CLI applications with newrelic.
It can be used for production profiling.

System requirements:
- PHP >= 8.2
- ext-newrelic (tested on 12.1+)

Installation (Composer):
```bash
composer require aeatech/cli-snapshot-profiler-newrelic-bundle
```

## Auto installation

You can install it with custom recipe.

```bash
composer config extra.symfony.allow-contrib true
composer config --json --merge extra.symfony.endpoint '["https://api.github.com/repos/AEATech/recipes/contents/index.json?ref=main", "flex://defaults"]'
composer require aeatech/cli-snapshot-profiler-newrelic-bundle
```

## Manual installation

Enable bundle in dev and prod env.

```php
// config/bundles.php

return [
    // ...
    AEATech\CLISnapshotProfilerNewrelicBundle\AEATechCLISnapshotProfilerNewrelicBundle::class => ['dev' => true, 'prod' => true],
    // ...
];
```

## Configuration

Symfony Flex generates a default configuration in config/packages/aea_tech_cli_snapshot_profiler_newrelic.yaml

```yaml
aea_tech_cli_snapshot_profiler_newrelic:
    # Enable/Disable profiling
    is_profiling_enabled: false

    # newrelic configuration
    newrelic:
        app_name: '%env(string:AEA_TECH_CLI_SNAPSHOT_PROFILER_NEWRELIC_APP_NAME)%'
        license: '%env(string:AEA_TECH_CLI_SNAPSHOT_PROFILER_NEWRELIC_LICENSE)%'

    ###
    # Event matched configuration - START
    ###
    event_matcher:
        # Enable/Disable all routes profiling
        is_profile_all_commands: false

        # Enable profile by command name (\AEATech\CLISnapshotProfilerEventSubscriber\EventMatcher\CommandEventMatcher)
        command:
            is_enabled: false
            name_list:
                - 'app:command'
    ###
    # Event matched configuration - END
    ###
```

## License

MIT License. See [LICENSE](./LICENSE) for details.