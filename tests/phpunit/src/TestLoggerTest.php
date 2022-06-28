<?php
/*
 * This file is part of dgfip-si1\test-logger.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DgfipSI1\testLoggerTests;

use DgfipSI1\testLogger\TestLogger;
use PHPUnit\Framework\TestCase;

/**
 * @covers DgfipSI1\TestLogger\TestLogger
 */
class TestLoggerTests extends TestCase
{
   /**
     * Test log method
     *
     */
    public function testsearchAndDelete(): void
    {
        $logger = new TestLogger;
        $logger->alert('foo');
        $this->assertFalse($logger->searchAndDeleteInfoRecords('foo'));
        $this->assertTrue($logger->searchAndDeleteAlertRecords('foo'));
        $this->assertFalse($logger->searchAndDeleteAlertRecords('foo'));
    }
    public function testsearch(): void
    {
        $logger = new TestLogger;
        $logger->alert('foo');
        $this->assertFalse($logger->searchInfoRecords('foo'));
        $this->assertTrue($logger->searchAlertRecords('foo'));
        $this->assertTrue($logger->searchAlertRecords('foo'));
    }
    public function testMessageList(): void
    {
        $logger = new TestLogger;
        $logger->alert('foo');
        $logger->alert('bar');
        $logger->info('foobar');

        $this->assertEquals(['foo', 'bar'], $logger->messageList('alert'));
        $this->assertEquals(['foobar'], $logger->messageList('info'));
        $this->assertEquals([], $logger->messageList('debug'));
    }
    public function testReset(): void
    {
        $logger = new TestLogger;
        $logger->alert('foo');
        $record = [ 'level'    => 'alert', 'message' => 'foo', 'context' => []];
        $this->assertEquals([ 'alert' => [$record]], $logger->getRecordsByLevel());
        $logger->reset();
        $this->assertEquals([], $logger->getRecordsByLevel());
    }
    public function testBadFunctionCall(): void
    {
        $logger = new TestLogger;
        $this->expectExceptionMessageMatches("/Call to undefined method/");
        $logger->foo();
    }

}