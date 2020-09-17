<?php

namespace Codedeploy\Uptime\Tests\Monitors;

use Codedeploy\Uptime\ApiResponse;
use Codedeploy\Uptime\Monitors\Composer;
use Codedeploy\Uptime\Tests\MocksApiRequests;
use Codedeploy\Uptime\Tests\TestCase;
use Codedeploy\Uptime\Uptime;

class ComposerTest extends TestCase
{
    use MocksApiRequests;

    /** @test */
    public function it_uploads_the_lock_file()
    {
        file_put_contents(base_path('composer.lock'), $lockContents = file_get_contents(__DIR__ . '/stubs/composer.lock'));

        $monitor = new Composer;
        $monitor->setCheckToken('check-token');

        Uptime::shouldReceive('composer')->with('check-token', json_decode($lockContents, true))->andReturn(ApiResponse::fromArray([
            'data' => [
                'count'           => 0,
                'vulnerabilities' => [],
            ],
        ]));

        $this->assertTrue($monitor->passes());
        $this->assertFalse($monitor->hasError());
    }

    /** @test */
    public function it_fails_when_vulnerabilities_are_found()
    {
        file_put_contents(base_path('composer.lock'), $lockContents = file_get_contents(__DIR__ . '/stubs/composer.lock'));

        $monitor = new Composer;
        $monitor->setCheckToken('check-token');

        Uptime::shouldReceive('composer')->with('check-token', json_decode($lockContents, true))->andReturn(ApiResponse::fromArray([
            'data' => [
                'count'           => 1,
                'vulnerabilities' => $vulnerabilities = [
                    [
                        'title' => 'Installed v5.6.29 of laravel/framework: Cookie serialization vulnerability',
                        'link'  => 'https://laravel.com/docs/5.6/upgrade#upgrade-5.6.30',
                        'cve'   => '',
                    ],
                ],
            ],
        ]));

        $this->assertFalse($monitor->passes());
        $this->assertTrue($monitor->hasError());

        $this->assertEquals([
            [
                'data' => [
                    'message'         => 'Found 1 vulnerability',
                    'vulnerabilities' => $vulnerabilities,
                ],
            ],
        ], $monitor->errors()['vulnerabilities_found']);
    }
}
