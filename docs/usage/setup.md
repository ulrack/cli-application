# Ulrack CLI Application - Setup

Setting up the CLI Application is quite simple.
After the package and its' dependencies are installed create a file from the
root of the application e.g.: `bin/application` with the following contents:

```php
#!/usr/bin/env php
<?php

use Ulrack\Kernel\Component\Kernel\Kernel;
use Ulrack\Kernel\Component\Kernel\Manager\CoreManager;
use Ulrack\CliApplication\Component\Application\CliApplication;

require_once __DIR__ . '/../vendor/autoload.php';


$coreManager = new CoreManager(__DIR__ . '/../');

$kernel = new Kernel(
    $coreManager
);

$cliApplication = new CliApplication($argv);

$kernel->run(
    $cliApplication
);

exit($cliApplication->getExitCode());
```

Then run the following command from the root of the application to make the file
executable:
```bash
chmod u+x bin/application
```

Then simply run the command `bin/application` from the root of the application.
This should show the output of the command list.

## Further reading

[Back to usage index](index.md)

[Create a command](create-a-command.md)
