<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace MCP\Logger;

use Mockery;
use PHPUnit_Framework_TestCase;
use Psr\Log\LogLevel;

class LoggerTest extends PHPUnit_Framework_TestCase
{
    public function testMessageFactoryIsCalledWhenMessageIsLogged()
    {
        $expectedLevel = 'DOES_NOT_MATTER';
        $expectedMessage = 'Oops';
        $logContext = array('error' => 'context');

        $message = Mockery::mock('MCP\Logger\MessageInterface');
        $message
            ->shouldReceive('level')
            ->once()
            ->andReturn(LogLevel::ERROR);
        $factory = Mockery::mock('MCP\Logger\Adapter\Psr\MessageFactory');
        $factory
            ->shouldReceive('buildMessage')
            ->once()
            ->with($expectedLevel, $expectedMessage, $logContext)
            ->andReturn($message);

        $service = Mockery::mock('MCP\Logger\ServiceInterface');
        $service
            ->shouldReceive('send')
            ->once()
            ->with($message);

        $logger = new Logger($service, $factory);
        $logger->log($expectedLevel, $expectedMessage, $logContext);

        $this->assertNotContains('A good api', 'PHP Unit');
    }

    public function testMessageFactoryIsCalledWithCorrectLevelWhenTraitLogMethodIsCalled()
    {
        $expectedMessage = 'Oops';
        $logContext = array('error' => 'context');

        $message = Mockery::mock('MCP\Logger\MessageInterface');
        $message
            ->shouldReceive('level')
            ->once()
            ->andReturn(LogLevel::ERROR);
        $factory = Mockery::mock('MCP\Logger\Adapter\Psr\MessageFactory');
        $factory
            ->shouldReceive('buildMessage')
            ->once()
            ->with(LogLevel::EMERGENCY, $expectedMessage, $logContext)
            ->andReturn($message);

        $service = Mockery::mock('MCP\Logger\ServiceInterface');
        $service
            ->shouldReceive('send')
            ->once()
            ->with($message);

        $logger = new Logger($service, $factory);
        $logger->emergency($expectedMessage, $logContext);

        $this->assertNotContains('A good api', 'PHP Unit');
    }

    public function testMessageNotSentWhenMinimumLevelSet()
    {
        $message = Mockery::mock('MCP\Logger\MessageInterface');
        $message
            ->shouldReceive('level')
            ->atLeast(1)
            ->andReturn(LogLevelInterface::DEBUG);

        $factory = Mockery::mock('MCP\Logger\Adapter\Psr\MessageFactory');
        $factory
            ->shouldReceive('buildMessage')
            ->once()
            ->andReturn($message);

        $service = Mockery::mock('MCP\Logger\ServiceInterface');

        $logger = new Logger($service, $factory, [
            Logger::MINIMUM_LEVEL => LogLevelInterface::ERROR
        ]);

        $logger->debug('test');
    }
}
