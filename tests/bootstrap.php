<?php

declare(strict_types=1);

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

new Dotenv()->bootEnv(dirname(__DIR__) . '/.env');

$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = $_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? 'test';

if ($_SERVER['APP_DEBUG']) {
    umask(0o000);
}

$kernel = new Kernel('test', true);
$kernel->boot();

$application = new Application($kernel);
$application->setAutoExit(false);

$commands = [
    new ArrayInput([
        'command'     => 'doctrine:database:drop',
        '--if-exists' => true,
        '--force'     => true,
        '--quiet'     => true,
    ]),
    new ArrayInput([
        'command' => 'doctrine:database:create',
        '--quiet' => true,
    ]),
    new ArrayInput([
        'command'          => 'doctrine:migration:migrate',
        '--no-interaction' => true,
        '--quiet'          => true,
    ]),
];

foreach ($commands as $command) {
    $status = $application->run($command);

    if (0 !== $status) {
        throw new RuntimeException('Error running bootstrap.php');
    }
}

$kernel->shutdown();
