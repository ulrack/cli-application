# Ulrack CLI Application - Create a command

After the application is setup, a command can be created. This requires 5 files:
- `composer.json`
- `locator.php`
- `configuration/command/my.command.json`
- `configuration/services/my.command.json`
- `src/Command/MyCommand.php`

The last three files can have any other (more appropriate) name.

## composer.json

The `composer.json` file is required to automatically load the `locator.php`
file. This can be easily done by adding the following node, to the file:
```json
{
    "autoload": {
        "psr-4": {
            "MyVendor\\MyCommandPackage\\": "src/"
        },
        "files": [
            "locator.php"
        ]
    }
}
```

## locator.php

The `locator.php` file is used to determine the root of the package, so
configuration can be autoloaded within the core of Ulrack. The contents of this
file should be the following:
```php
<?php

use GrizzIt\Configuration\Component\Configuration\PackageLocator;

PackageLocator::registerLocation(__DIR__);

```

## configuration/command/my.command.json

This file will take care of the registration of the command. The contents will
look something along the lines of:
```json
{
    "command": "my-command",
    "description": "Executes my command.",
    "service": "services.command.my.command"
}
```

This version would then be executed with `bin/application my-command`.

Optionally an additional node can be passed in this file to group the command.
This node is `parent` and would require another file in order to setup the group.
This would look something like this:

`my.command.json`
```json
{
    "parent": "my",
    "command": "command",
    "description": "Executes my command.",
    "service": "services.command.my.command"
}
```

`my.command.group.json`
```json
{
    "command": "my",
    "description": "My commands are located here."
}
```

This version would be executed with `bin/application my command`.

## configuration/services/my.command.json

This file is required to register the service, which will be retrieved in the
command router. The contents of this file will look like the following:
```json
{
    "command.my.command": {
        "class": "\\MyVendor\\MyCommandPackage\\Command\\MyCommand"
    }
}
```

## src/Command/MyCommand.php

The command uses the `CommandInterface` from the `ulrack/command` package. The
implementation would look like this:
```php
<?php

namespace MyVendor\MyCommandPackage\Command;

use Ulrack\Command\Common\Command\InputInterface;
use Ulrack\Command\Common\Command\OutputInterface;
use Ulrack\Command\Common\Command\CommandInterface;

class MyCommand implements CommandInterface
{
    /**
     * Executes the command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     */
    public function __invoke(
        InputInterface $input,
        OutputInterface $output
    ): void {
        $output->writeLine('Hello world!');
    }
}

```

To load the new configuration clear the caches by running:
```bash
bin/application cache clear
```

Then to validate the configuration run the command:
```bash
bin/application validate configuration
```

## Further reading

[Back to usage index](index.md)

[Setup](setup.md)
