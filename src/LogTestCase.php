<?php
/*
 * This file is part of drupalindus
 */
namespace DgfipSI1\testLogger;

use PHPUnit\Framework\TestCase;

/**
 * @method bool assertEmergencyInLog(string $message, bool $interpolate = false)
 * @method bool assertAlertInLog(string $message, bool $interpolate = false)
 * @method bool assertCriticalInLog(string $message, bool $interpolate = false)
 * @method bool assertErrorInLog(string $message, bool $interpolate = false)
 * @method bool assertWarningInLog(string $message, bool $interpolate = false)
 * @method bool assertNoticeInLog(string $message, bool $interpolate = false)
 * @method bool assertInfoInLog($message, bool $interpolate = false)
 * @method bool assertDebugInLog($message, bool $interpolate = false)
 *
 * @method bool assertEmergencyLogEmpty()
 * @method bool assertAlertLogEmpty()
 * @method bool assertCriticalLogEmpty()
 * @method bool assertErrorLogEmpty()
 * @method bool assertWarningLogEmpty()
 * @method bool assertNoticeLogEmptyg()
 * @method bool assertInfoLogEmpty()
 * @method bool assertDebugLogEmpty()
 */
// @codingStandardsIgnoreStart
abstract class LogTestCase extends TestCase
{
    // @codingStandardsIgnoreEnd
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
                $args = array_merge([$level], $args);
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
     * @param string $level
     * @param string $message
     * @param bool   $interpolate
     */
    public function assertInLog($level, $message, $interpolate = false): void
    {
        $failMsg = "message [$level]$message not found in log";
        $this->assertTrue($this->logger->searchAndDeleteRecords($message, $level, $interpolate), $failMsg);
    }
    /**
     * assertLogEmpty function : assert that no more messages of level $level are left in log
     *
     * @param string|null $level
     *
     * @return void
     */
    public function assertLogEmpty($level = null): void
    {
        $recordsByLevel = $this->logger->getRecordsByLevel();
        if (null === $level) {
            foreach ($recordsByLevel as $messages) {
                $this->assertEquals(0, count($messages));
            }
        } else {
            if (array_key_exists($level, $recordsByLevel)) {
                $messages = count($recordsByLevel[$level]);
                $explain = ucfirst($level)." messages left : $messages\n".print_r($recordsByLevel[$level], true);
                $this->assertEquals(0, $messages, $explain);
            }
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
        $count = 0;
        foreach ($recordsByLevel as $level => $records) {
            if ('debug' === $level) {
                continue;
            }
            $msg .= count($records)." message(s) left in level $level\n - ";
            $msg .= implode("\n - ", $this->logger->messageList($level))."\n";
            $count += count($records);
        }
        $this->assertEquals(0, $count, "Fail asserting that log is empty :\n$msg");
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
    public function showLogs(bool $cut = false): void
    {
        $this->showNoDebugLogs($cut);
        $this->showDebugLogs($cut);
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
                printf($fmt, "  => ".$this->logger->interpolate($record['message'], $record['context']));
                $printed++;
            }
        }
        print "$printed message(s) in logs (excluding debug)\n";
        print "=============================================\n";
    }
    /**
     *
     * @param bool $cut
     */
    public function showDebugLogs(bool $cut = false): void
    {
        $recordsByLevel = $this->logger->getRecordsByLevel();
        $printed = 0;
        $fmt = $cut ? "    %-76.76s\n" : "    %s\n";
        print "\n=============================================\n";
        foreach (array_keys($recordsByLevel) as $level) {
            if ('debug' !== $level) {
                continue;
            }
            print "LEVEL $level\n";
            foreach ($recordsByLevel[$level] as $record) {
                printf($fmt, $record['message']);
                printf($fmt, "  => ".$this->logger->interpolate($record['message'], $record['context']));
                $printed++;
            }
        }
        print "$printed message(s) in debug logs\n";
        print "=============================================\n";
    }
}
