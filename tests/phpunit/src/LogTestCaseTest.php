<?php
/*
 * This file is part of dgfip-si1\test-logger.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DgfipSI1\testLoggerTests;

use DgfipSI1\testLogger\LogTestCase;
use DgfipSI1\testLogger\TestLogger;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * logTestCase tests
 *
 * @uses DgfipSI1\testLogger\LogTestCase
 * @uses DgfipSI1\testLogger\TestLogger
 */
class LogTestCaseTest extends TestCase
{
    /** @var LogTestCaseConcrete $test */
    protected $test;

    /** @var TestLogger */
    protected $logger;

    /**
     * @inheritDoc
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->test = new LogTestCaseConcrete();
        $class = new ReflectionClass(LogTestCase::class);

        $lg = $class->getProperty('logger');
        $lg->setAccessible(true);

        $this->logger = new TestLogger();
        $lg->setValue($this->test, $this->logger);
    }

   /**
     * Test __call method
     *
     * @covers \DgfipSI1\testLogger\LogTestCase
     */
    public function testCallFunction(): void
    {
        $this->expectExceptionMessageMatches("/Call to undefined method/");
        /** @phpstan-ignore-next-line */
        $this->test->foo();
    }
   /**
     * Test __call method and assertInLog
     *
     * @covers \DgfipSI1\testLogger\LogTestCase::__call
     * @covers \DgfipSI1\testLogger\LogTestCase::assertInLog
     * @covers \DgfipSI1\testLogger\LogTestCase::assertNotInLog
     *
     */
    public function testAssertInLog(): void
    {
        $this->logger->alert('test message with {name}', [ 'name' => 'foo']);
        $this->logger->info('test message with {name}', [ 'name' => 'bar']);
        $this->test->assertAlertNotInLog('test message with {name}', true); // interpolated
        $this->test->assertAlertNotInLog('message with', false, true);      // exact match
        $this->test->assertAlertInLog('test message with');                 // aproximate
        $this->test->assertAlertLogEmpty();
        $this->test->assertInfoNotInLog('test message with bar', false);    // not interpolated
        $this->test->assertInfoNotInLog('message with bar', true, true);    // exact search
        $this->test->assertInfoInLog('test message with bar', true, true);  // interpolated & exact
        $this->test->assertInfoLogEmpty();
    }
    /**
     * Test __call method and assertInContextLog
     *
     * @covers \DgfipSI1\testLogger\LogTestCase::__call
     * @covers \DgfipSI1\testLogger\LogTestCase::assertInContextLog
     * @covers \DgfipSI1\testLogger\LogTestCase::assertNotInContextLog
     *
     */
    public function testAssertInContextLog(): void
    {
        $this->logger->alert('test alert message', [ 'name' => 'foo']);
        $this->logger->info('test info message', [ 'name' => 'bar']);
        $this->test->assertAlertNotInContextLog('test alert message', [ 'name' => '__']);
        $this->test->assertAlertInContextLog('test alert message', [ 'name' => 'foo']);
        $this->test->assertInfoNotInContextLog('test alert message', [ 'name' => '__']);
        $this->test->assertInfoInContextLog('test info message', [ 'name' => 'bar']);
    }
   /**
     * Test assertLogEmpty method
     *
     * @covers \DgfipSI1\testLogger\LogTestCase::assertLogEmpty
     *
     */
    public function testAssertLogEmpty(): void
    {
        $this->logger->alert('test message with {name}', [ 'name' => 'foo']);
        $this->test->assertAlertInLog('test message with {name}');
        $this->test->assertAlertLogEmpty();
        $this->test->assertLogEmpty();
    }
   /**
     * Test assertNoMoreProdMessages method
     *
     * @covers \DgfipSI1\testLogger\LogTestCase::assertNoMoreProdMessages
     *
     */
    public function testAssertNoMoreProdMessages(): void
    {
        $this->logger->alert('test message with {name}', [ 'name' => 'foo']);
        $this->test->assertAlertInLog('test message with {name}');
        $this->logger->debug('debugging...');
        $this->test->assertNoMoreProdMessages();
    }
   /**
     * Test logReset method
     *
     * @covers \DgfipSI1\testLogger\LogTestCase::logReset
     *
     * @uses \DgfipSI1\testLogger\LogTestCase::assertNoMoreProdMessages
     *
     */
    public function testLogReset(): void
    {
        $this->logger->alert('test message with {name}', [ 'name' => 'foo']);
        $this->test->logReset();
        $this->test->assertNoMoreProdMessages();
    }
   /**
     * Test showNoDebugLogs method
     *
     * @covers \DgfipSI1\testLogger\LogTestCase::showNoDebugLogs
     *
     */
    public function testShowNoDebugLogs(): void
    {
        $this->expectOutputString($this->getAlertText());
        $this->logger->alert('test message with {name}', [ 'name' => 'foo']);
        $this->logger->debug('debug message...');
        $this->test->showNoDebugLogs();
    }
   /**
     * Test showDebugLogs method
     *
     * @covers \DgfipSI1\testLogger\LogTestCase::showDebugLogs
     *
     */
    public function testShowDebugLogs(): void
    {
        $this->expectOutputString($this->getDebugText());
        $this->logger->alert('test message with {name}', [ 'name' => 'foo']);
        $this->logger->debug('debug message...');
        $this->test->showDebugLogs();
    }
    /**
     * Test showLogs method
     *
     * @covers \DgfipSI1\testLogger\LogTestCase::showLogs
     *
     */
    public function testShowLogs(): void
    {
        $this->expectOutputString($this->getAlertText().$this->getDebugText());
        $this->logger->alert('test message with {name}', [ 'name' => 'foo']);
        $this->logger->debug('debug message...');
        $this->test->showLogs();
    }
    /**
     * getDebug
     *
     * @return string
     */
    protected function getDebugText()
    {
        $debugLog  = "\n=============================================\n";
        $debugLog .= "LEVEL debug\n";
        $debugLog .= "    debug message... - Context: \n";
        $debugLog .= "      => debug message...\n";
        $debugLog .= "1 message(s) in debug logs\n";
        $debugLog .= "=============================================\n";

        return $debugLog;
    }
    /**
     * get Alert
     *
     * @return string
     */
    protected function getAlertText()
    {
        $alertLog  = "\n=============================================\n";
        $alertLog .= "LEVEL alert\n";
        $alertLog .= "    test message with {name} - Context: name=foo\n";
        $alertLog .= "      => test message with foo\n";
        $alertLog .= "1 message(s) in logs (excluding debug)\n";
        $alertLog .= "=============================================\n";

        return $alertLog;
    }
}
