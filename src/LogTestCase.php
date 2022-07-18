<?php
/*
 * This file is part of drupalindus
 */
namespace DgfipSI1\testLogger;

use PHPUnit\Framework\TestCase;

/**
 * @method bool assertEmergencyInLog(string $message)
 * @method bool assertAlertInLog(string $message)
 * @method bool assertCriticalInLog(string $message)
 * @method bool assertErrorInLog(string $message)
 * @method bool assertWarningInLog(string $message)
 * @method bool assertNoticeInLog(string $message)
 * @method bool assertInfoInLog($message)
 * @method bool assertDebugInLog($message)
 */
class LogTestCase extends TestCase
{
    /** @var TestLogger $logger */
    protected $logger;

    /**
     * function assertWarningInLog        => assertInLog(warning)
     * function assertLogWarningEmpty     => assertLogEmpty(warning)
     *
     * @param string       $method
     * @param array<mixed> $args
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        $levels = 'Debug|Info|Notice|Warning|Error|Critical|Alert|Emergency';
        if (preg_match('/(assert.*)('.$levels.')(.*)/', $method, $matches) > 0) {
            $genericMethod = $matches[1].$matches[3];
            $level = lcfirst($matches[2]);
            if (method_exists($this, $genericMethod)) {
                $args[] = $level;
                $callback = [$this, $genericMethod];
                if (is_callable($callback)) {
                    return call_user_func_array($callback, $args);
                }
            }
        }
        throw new \BadMethodCallException(sprintf("Call to undefined method '%s::%s()'", get_class($this), $method));
    }
    /**
     * Assert message is in log level $level, then purge message
     *
     * @param string $message
     * @param string $level
     */
    public function assertInLog($message, $level): void
    {
        $failMsg = "message [$level]$message not found in log";
        $this->assertTrue($this->logger->searchAndDeleteRecords($message, $level), $failMsg);
    }
    /**
     * assertLogEmpty function : assert that no more messages of level $level are left in log
     *
     * @param string $level
     *
     * @return void
     */
    public function assertLogEmpty($level): void
    {
        $recordsByLevel = $this->logger->getRecordsByLevel();
        if (array_key_exists($level, $recordsByLevel)) {
            $messages = count($recordsByLevel[$level]);
            $explain = ucfirst($level)." messages left : $messages\n".print_r($recordsByLevel[$level], true);
            $this->assertEquals(0, $messages, $explain);
        }
    }
    /**
     * Assert all logLevels (except Debug) are empty
     *
     * @return void
     */
    public function assertNoMoreProdMessages()
    {
        $recordsByLevel = $this->logger->getRecordsByLevel();
        $msg = '';
        foreach ($recordsByLevel as $level => $records) {
            if ('debug' === $level) {
                continue;
            }
            if (count($records) > 0) {
                $msg .= count($records)." message(s) left in level $level\n - ";
                $msg .= implode("\n - ", $this->logger->messageList($level))."\n";
            }
        }
        $this->assertEmpty($msg, "Fail asserting that log is empty :\n$msg");
    }

    /**
     * empty logs
     *
     * @return void
     */
    public function logReset(): void
    {
        $this->logger->reset();
    }
    /**
     *
     * @param bool $cut
     */
    public function showNoDebugLogs(bool $cut = false): void
    {
        $recordsByLevel = $this->logger->getRecordsByLevel();
        $printed = 0;
        $fmt = $cut ? "    %-76.76s\n" : "    %s\n";
        print "\n=============================================\n";
        foreach (array_keys($recordsByLevel) as $level) {
            if ('debug' === $level) {
                continue;
            }
            print "LEVEL $level\n";
            foreach ($recordsByLevel[$level] as $record) {
                printf($fmt, $record['message']);
                $printed++;
            }
        }
        print "$printed message(s) in logs (excluding debug)\n";
        print "=============================================\n";
    }
}
