<?php
/*
 * This file is part of drupalindus
 */
namespace DgfipSI1\testLogger;

use PHPUnit\Framework\TestCase;

/**
 * @method bool assertEmergencyInLog(string $message, bool $interpolate = false, $exact=false)
 * @method bool assertAlertInLog(string $message, bool $interpolate = false, $exact=false)
 * @method bool assertCriticalInLog(string $message, bool $interpolate = false, $exact=false)
 * @method bool assertErrorInLog(string $message, bool $interpolate = false, $exact=false)
 * @method bool assertWarningInLog(string $message, bool $interpolate = false, $exact=false)
 * @method bool assertNoticeInLog(string $message, bool $interpolate = false, $exact=false)
 * @method bool assertInfoInLog($message, bool $interpolate = false, $exact=false)
 * @method bool assertDebugInLog($message, bool $interpolate = false, $exact=false)
 *
 * @method bool assertEmergencyNotInLog(string $message, bool $interpolate = false, $exact=false)
 * @method bool assertAlertNotInLog(string $message, bool $interpolate = false, $exact=false)
 * @method bool assertCriticalNotInLog(string $message, bool $interpolate = false, $exact=false)
 * @method bool assertErrorNotInLog(string $message, bool $interpolate = false, $exact=false)
 * @method bool assertWarningNotInLog(string $message, bool $interpolate = false, $exact=false)
 * @method bool assertNoticeNotInLog(string $message, bool $interpolate = false, $exact=false)
 * @method bool assertInfoNotInLog($message, bool $interpolate = false, $exact=false)
 * @method bool assertDebugNotInLog($message, bool $interpolate = false, $exact=false)
 *
 * @method bool assertEmergencyInContextLog(string $message, array $withCtxt, $exact=false)
 * @method bool assertAlertInContextLog(string $message, array $withCtxt, $exact=false)
 * @method bool assertCriticalInContextLog(string $message, array $withCtxt, $exact=false)
 * @method bool assertErrorInContextLog(string $message, array $withCtxt, $exact=false)
 * @method bool assertWarningInContextLog(string $message, array $withCtxt, $exact=false)
 * @method bool assertNoticeInContextLog(string $message, array $withCtxt, $exact=false)
 * @method bool assertInfoInContextLog($message, array $withCtxt, $exact=false)
 * @method bool assertDebugInContextLog($message, array $withCtxt, $exact=false)
 *
 * @method bool assertEmergencyNotInContextLog(string $message, array $withCtxt, $exact=false)
 * @method bool assertAlertNotInContextLog(string $message, array $withCtxt, $exact=false)
 * @method bool assertCriticalNotInContextLog(string $message, array $withCtxt, $exact=false)
 * @method bool assertErrorNotInContextLog(string $message, array $withCtxt, $exact=false)
 * @method bool assertWarningNotInContextLog(string $message, array $withCtxt, $exact=false)
 * @method bool assertNoticeNotInContextLog(string $message, array $withCtxt, $exact=false)
 * @method bool assertInfoNotInContextLog($message, array $withCtxt, $exact=false)
 * @method bool assertDebugNotInContextLog($message, array $withCtxt, $exact=false)
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
     * @param string               $level
     * @param string               $message
     * @param array<string,string> $withCtxt
     * @param bool                 $exact
     */
    public function assertInContextLog($level, $message, $withCtxt, $exact = false): void
    {
        $failMsg = "message [$level]$message not found in log - filter by context ".print_r($withCtxt, true)."\n";
        $ret = $this->logger->searchAndDeleteRecords($message, $level, false, $exact, $withCtxt);
        self::assertTrue($ret, $failMsg);
    }
    /**
     * Assert message is NOT in log level $level, then purge message
     *
     * @param string               $level
     * @param string               $message
     * @param array<string,string> $withCtxt
     * @param bool                 $exact
     */
    public function assertNotInContextLog($level, $message, $withCtxt, $exact = false): void
    {
        $failMsg = "message [$level]$message not found in log - filter by context ".print_r($withCtxt, true)."\n";
        $ret = $this->logger->searchAndDeleteRecords($message, $level, false, $exact, $withCtxt);
        self::assertFalse($ret, $failMsg);
    }
    /**
     * Assert message is in log level $level, then purge message
     *
     * @param string $level
     * @param string $message
     * @param bool   $interpolate
     * @param bool   $exact
     */
    public function assertInLog($level, $message, $interpolate = false, $exact = false): void
    {
        $failMsg = "message [$level]$message not found in log";
        self::assertTrue($this->logger->searchAndDeleteRecords($message, $level, $interpolate, $exact), $failMsg);
    }
    /**
     * Assert message is NOT in log level $level, then purge message
     *
     * @param string $level
     * @param string $message
     * @param bool   $interpolate
     * @param bool   $exact
     */
    public function assertNotInLog($level, $message, $interpolate = false, $exact = false): void
    {
        $failMsg = "message [$level]$message not found in log";
        self::assertFalse($this->logger->searchAndDeleteRecords($message, $level, $interpolate, $exact), $failMsg);
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
                self::assertEquals(0, count($messages));
            }
        } else {
            if (array_key_exists($level, $recordsByLevel)) {
                $messages = count($recordsByLevel[$level]);
                $explain = ucfirst($level)." messages left : $messages\n".print_r($recordsByLevel[$level], true);
                self::assertEquals(0, $messages, $explain);
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
        self::assertEquals(0, $count, "Fail asserting that log is empty :\n$msg");
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
                $line = $record['message'];
                $callback = fn(string $k, string $v): string => "$k=$v";
                $ctxArray = array_map($callback, array_keys($record['context']), array_values($record['context']));
                $contextStr = implode(', ', $ctxArray) ;
                printf($fmt, $record['message']." - Context: $contextStr");
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
                $callback = fn(string $k, string $v): string => "$k=$v";
                $ctxArray = array_map($callback, array_keys($record['context']), array_values($record['context']));
                $contextStr = implode(', ', $ctxArray) ;
                printf($fmt, $record['message']." - Context: $contextStr");
                printf($fmt, "  => ".$this->logger->interpolate($record['message'], $record['context']));
                $printed++;
            }
        }
        print "$printed message(s) in debug logs\n";
        print "=============================================\n";
    }
}
