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
        $logger->alert('foo bar');
        // test on level
        self::assertFalse($logger->searchAndDeleteRecords('foo', 'info'));

        // test exact / inexact
        self::assertFalse($logger->searchAndDeleteRecords('foo', 'alert', exact: true));
        self::assertTrue($logger->searchAndDeleteRecords('foo', 'alert'));
        self::assertEquals(['alert' => []], $logger->getRecordsByLevel());

        // test interpolate / do not interpolate
        $logger->alert('this message is called {name}', ['name' => 'foo']);
        self::assertFalse($logger->searchAndDeleteRecords('is called foo', 'alert', false));
        self::assertTrue($logger->searchAndDeleteRecords('is called foo', 'alert', true));
        self::assertEquals(['alert' => []], $logger->getRecordsByLevel());

        // test with context
        $msg = 'this message is called {name}';
        $ctx = ['foo' => 'fooValue', 'bar' => 'barValue'];
        $logger->alert($msg, $ctx);
        self::assertFalse($logger->searchAndDeleteRecords('message', 'alert', withCtxt: ['foo' => "bar"]));
        self::assertTrue($logger->searchAndDeleteRecords('message', 'alert', withCtxt: ['foo' => 'fooValue']));

        $logger->alert($msg, ['foo' => 'fooValue']);
        self::assertFalse($logger->searchAndDeleteRecords('message', 'alert', withCtxt: $ctx));

        $logger->alert($msg, $ctx);
        self::assertTrue($logger->searchAndDeleteRecords('message', 'alert', withCtxt: $ctx));

        $logger->alert($msg, $ctx);
        self::assertFalse($logger->searchAndDeleteRecords('message', 'alert', withCtxt: $ctx, exact: true));
        self::assertTrue($logger->searchAndDeleteRecords($msg, 'alert', withCtxt: $ctx, exact: true));
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
        self::assertFalse($logger->searchAndDeleteRecords('is called foo', 'info'));
        self::assertFalse($logger->searchAndDeleteRecords('is called foo', 'alert', false));
        self::assertTrue($logger->searchAndDeleteRecords('is called foo', 'alert', true));
        self::assertFalse($logger->searchAndDeleteRecords('is called foo', 'alert', true));
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
        self::assertFalse($logger->searchAndDeleteRecords('foo', 'info'));
        self::assertTrue($logger->searchAndDeleteRecords('foo', 'alert'));
        self::assertFalse($logger->searchAndDeleteRecords('foo', 'alert'));

        $logger = new TestLogger();
        $logger->setCallers('otherMethod');
        $logger->alert('foo');
        self::assertFalse($logger->searchAndDeleteRecords('foo', 'info'));
        self::assertFalse($logger->searchAndDeleteRecords('foo', 'alert'));

        // test log from closure
        $logger = new TestLogger(['testCallerFilter']);

        $logger->alert('foo');
        $alertClosure = function ($message) use ($logger) {
            $logger->alert($message);
        };
        $alertClosure("message1");
        $alertClosure("message2");
        self::assertTrue($logger->searchAndDeleteRecords('message1', 'alert'));
        self::assertTrue($logger->searchAndDeleteRecords('message2', 'alert'));
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
        self::assertFalse($logger->searchRecords('foo', 'info'));
        self::assertTrue($logger->searchRecords('foo', 'alert'));
        self::assertTrue($logger->searchRecords('foo', 'alert'));
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

        self::assertEquals(["foo\n    => foo", "bar\n    => bar"], $logger->messageList('alert'));
        self::assertEquals(["foobar\n    => foobar"], $logger->messageList('info'));
        self::assertEquals([], $logger->messageList('debug'));
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
        self::assertEquals([ 'alert' => [$record]], $logger->getRecordsByLevel());
        $logger->reset();
        self::assertEquals([], $logger->getRecordsByLevel());
    }
}
