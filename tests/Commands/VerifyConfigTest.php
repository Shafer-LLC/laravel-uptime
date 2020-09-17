<?php

namespace Codedeploy\Uptime\Tests;

use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Codedeploy\Uptime\ApiResponse;
use Codedeploy\Uptime\Commands\VerifyConfig;
use Codedeploy\Uptime\Tests\TestCase;
use Codedeploy\Uptime\Uptime;


class VerifyConfigTest extends TestCase
{
    /** @test */
    public function it_succeeds_when_the_api_returns_200()
    {
        Uptime::shouldReceive('application')
            ->andReturn(ApiResponse::fromArray(['data' => ['app_id' => 1]], 200));

        $input = $this->mock(InputInterface::class);
        $input->shouldIgnoreMissing();

        $output = $this->mock(ConsoleOutput::class);
        $output->shouldReceive('getVerbosity')->andReturn(16);
        $output->shouldReceive('getFormatter')->andReturn(new OutputFormatter);
        $output->shouldReceive('writeln')->withArgs(function ($line) {
            return $line === '<info>Application authentication with cronmonitor.dev succeeded.</info>';
        });

        $command = new VerifyConfig;
        $command->setLaravel(app());
        $command->run($input, $output);
    }

    /** @test */
    public function it_fails_when_the_application_fetching_fails()
    {
        Uptime::shouldReceive('application')
            ->andReturn(ApiResponse::fromArray([], 401));

        $input = $this->mock(InputInterface::class);
        $input->shouldIgnoreMissing();

        $output = $this->mock(ConsoleOutput::class);
        $output->shouldReceive('getVerbosity')->andReturn(32);
        $output->shouldReceive('getFormatter')->andReturn(new OutputFormatter);
        $output->shouldReceive('writeln')->withArgs(function ($line) {
            return $line === '<error>Application authentication with cronmonitor.dev failed.</error>';
        });

        $command = new VerifyConfig;
        $command->setLaravel(app());
        $command->run($input, $output);
    }
}
