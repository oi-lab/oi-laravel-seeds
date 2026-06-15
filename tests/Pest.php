<?php

use Illuminate\Console\Command;
use Illuminate\Console\OutputStyle;
use OiLab\OiLaravelSeeds\Tests\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

uses(TestCase::class)->in(__DIR__);

/**
 * Build a real, output-bound console command for driving a seeder in isolation.
 *
 * Using $this->artisan() here would return a PendingCommand that is never run,
 * leaking a deferred run() into teardown once the container is gone.
 */
function fakeSeederCommand(): Command
{
    $command = new Command;
    $command->setLaravel(app());
    $command->setOutput(new OutputStyle(new ArrayInput([]), new BufferedOutput));

    return $command;
}
