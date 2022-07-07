<?php
/*
 * This file is part of dgfip-si1/test-logger
 */

namespace DgfipSI1\testLogger;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

/**
 * PhpUnit TestCase with Logger assertions
 *
 */

/**
 * Test logger
 * @phpstan-type record array{level: string, message: string, context: array<string,string>}
 */
class TestLogger extends AbstractLogger implements LoggerInterface
{
    /** @var array<string,array<record>>  */
    public $recordsByLevel = [];

    /**
     * @inheritdoc
     *
     * @param string               $level
     * @param array<string,string> $context
     */
    public function log($level, $message, array $context = [])
    {
        $record = [
            'level'    => $level,
            'message' => $message,
            'context' => $context,
        ];

        $this->recordsByLevel[$level][] = $record;
    }

    /**
     * function fooInfoBar     => FooRecordBar(info)   => Ecrire getAndDelete
     * function fooInfoRecords => FooRecords(info)
     *
     * @param string       $method
     * @param array<mixed> $args
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (preg_match('/(.*)(Debug|Info|Notice|Warning|Error|Critical|Alert|Emergency)(.*)/', $method, $matches) > 0) {
            $genericMethod = $matches[1].('Records' !== $matches[3] ? 'Record' : '').$matches[3];
            $level = strtolower($matches[2]);
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
     * searchAndDeleteInfoRecords, searchAndDeleteWarningRecords, ...
     *
     * @param string $message
     * @param string $level
     *
     * @return bool
     */
    public function searchAndDeleteRecords($message, $level)
    {
        $toDelete = [];
        $ret = false;
        if (isset($this->recordsByLevel[$level])) {
            foreach ($this->recordsByLevel[$level] as $i => $rec) {
                if (strpos($rec['message'], $message) !== false) {
                    $ret = true;
                    $toDelete[] = $i;
                }
            }
        }
        if ($toDelete) {
            foreach ($toDelete as $index) {
                unset($this->recordsByLevel[$level][$index]);
            }
        }

        return $ret;
    }
    /**
     * searchInfoRecords, searchWarningRecords, ...
     *
     * @param string $message
     * @param string $level
     *
     * @return bool
     */
    public function searchRecords($message, $level)
    {
        if (isset($this->recordsByLevel[$level])) {
            foreach ($this->recordsByLevel[$level] as $rec) {
                if (strpos($rec['message'], $message) !== false) {
                    return true;
                }
            }
        }

        return false;
    }
    /** return message list for this level
     * @param string $level
     *
     * @return array<string>
     */
    public function messageList($level)
    {
        if (!array_key_exists($level, $this->recordsByLevel)) {
            return [];
        }
        $getMsg = function ($record) {
            return $record['message'];
        };

        return array_map($getMsg, $this->recordsByLevel[$level]);
    }
    /**
     * getRecordsByLevel : getter for $recordsByLevel
     *
     * @return array<string,array<record>>
     */
    public function getRecordsByLevel()
    {
        return $this->recordsByLevel;
    }
    /**
     * reset()
     *
     * @return void
     */
    public function reset()
    {
        $this->recordsByLevel = [];
    }
}
