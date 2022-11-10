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
class TestLoggerTest extends TestCase
{
   /**
     * Test log method
     *
     */
    public function testsearchAndDelete(): void
    {
        $logger = new TestLogger();
        $logger->alert('foo');
        $this->assertFalse($logger->searchAndDeleteRecords('foo', 'info'));
        $this->assertTrue($logger->searchAndDeleteRecords('foo', 'alert'));
        $this->assertFalse($logger->searchAndDeleteRecords('foo', 'alert'));
    }
    /**
     * test Interpolation
     *
     * @return void
     */
    public function testInterpolation(): void
    {
        $logger = new TestLogger();
        $logger->alert('this message is called {name}', ['name' => 'foo']);
        $this->assertFalse($logger->searchAndDeleteRecords('is called foo', 'info'));
        $this->assertFalse($logger->searchAndDeleteRecords('is called foo', 'alert', false));
        $this->assertTrue($logger->searchAndDeleteRecords('is called foo', 'alert', true));
        $this->assertFalse($logger->searchAndDeleteRecords('is called foo', 'alert', true));
    }
    /**
     * test Caller Filter
     *
     * @return void
     */
    public function testCallerFilter(): void
    {
        $logger = new TestLogger(['testCallerFilter']);
        $logger->alert('foo');
        $this->assertFalse($logger->searchAndDeleteRecords('foo', 'info'));
        $this->assertTrue($logger->searchAndDeleteRecords('foo', 'alert'));
        $this->assertFalse($logger->searchAndDeleteRecords('foo', 'alert'));

        $logger = new TestLogger(['otherMethod']);
        $logger->alert('foo');
        $this->assertFalse($logger->searchAndDeleteRecords('foo', 'info'));
        $this->assertFalse($logger->searchAndDeleteRecords('foo', 'alert'));

        // test log from closure
        $logger = new TestLogger(['testCallerFilter']);

        $logger->alert('foo');
        $alertClosure = function ($message) use ($logger) {
            $logger->alert($message);
        };
        $alertClosure("message1");
        $alertClosure("message2");
        $this->assertTrue($logger->searchAndDeleteRecords('message1', 'alert'));
        $this->assertTrue($logger->searchAndDeleteRecords('message2', 'alert'));
    }
    /**
     * test searchRecords
     *
     * @return void
     */
    public function testsearch(): void
    {
        $logger = new TestLogger();
        $logger->alert('foo');
        $this->assertFalse($logger->searchRecords('foo', 'info'));
        $this->assertTrue($logger->searchRecords('foo', 'alert'));
        $this->assertTrue($logger->searchRecords('foo', 'alert'));
    }
    /**
     * test messageList
     *
     * @return void
     */
    public function testMessageList(): void
    {
        $logger = new TestLogger();
        $logger->alert('foo');
        $logger->alert('bar');
        $logger->info('foobar');

        $this->assertEquals(["foo\n    => foo", "bar\n    => bar"], $logger->messageList('alert'));
        $this->assertEquals(["foobar\n    => foobar"], $logger->messageList('info'));
        $this->assertEquals([], $logger->messageList('debug'));
    }
    /**
     * test reset
     *
     * @return void
     */
    public function testReset(): void
    {
        $logger = new TestLogger();
        $logger->alert('foo');
        $record = [ 'level'    => 'alert', 'message' => 'foo', 'context' => []];
        $this->assertEquals([ 'alert' => [$record]], $logger->getRecordsByLevel());
        $logger->reset();
        $this->assertEquals([], $logger->getRecordsByLevel());
    }
}
