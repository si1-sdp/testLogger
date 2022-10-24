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

    /** @var array<string>  $callers */
    public $callers;

    /**
     * Constructor
     *
     * @param array<string> $callers
     *
     * @return void
     */
    public function __construct($callers = [])
    {
        $this->setCallers($callers);
    }
    /**
     * set authorized caller methods
     *
     * @param array<string> $callers
     *
     * @return void
     */
    public function setCallers($callers)
    {
        $this->callers = $callers;
    }
    /**
     * @inheritdoc
     *
     * @param string                $level
     * @param string|\Stringable   $message
     * @param array<string,string> $context
     *
     * @return void
     */
    public function log($level, string|\Stringable $message, array $context = []): void
    {
        // log only from defined caller methods
        // find first nonClosure method
        if (0 !== count($this->callers)) {
            // trace[0] : log, trace[1] : debug|warning|..., real caller is in trace[2] or more if called from closure
            $trace = debug_backtrace();
            $found = false;
            for ($lvl = 2; $lvl < count($trace); $lvl++) {
                $closure = (false !== strpos($trace[$lvl]['function'], '{closure}'));
                if ($closure || !array_key_exists('class', $trace[$lvl])) {
                    continue;
                }
                if (in_array($trace[$lvl]['function'], $this->callers)) {
                    $found = true;
                }
                break;
            }
            if (!$found) {
                return;
            }
        }
        $record = [
            'level'    => $level,
            'message' => $message,
            'context' => $context,
        ];
        $this->recordsByLevel[$level][] = $record;
    }

    /**
     * searchAndDeleteInfoRecords, searchAndDeleteWarningRecords, ...
     *
     * @param string $message
     * @param string $level
     * @param bool   $interpolate
     *
     * @return bool
     */
    public function searchAndDeleteRecords($message, $level, $interpolate = false)
    {
        $toDelete = [];
        $ret = false;

        if (isset($this->recordsByLevel[$level])) {
            foreach ($this->recordsByLevel[$level] as $i => $rec) {
                $recMessage = $rec['message'];
                if ($interpolate) {
                    $recMessage = $this->interpolate($rec['message'], $rec['context']);
                }
                if (strpos($recMessage, $message) !== false) {
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
            return $record['message']."\n    => ".$this->interpolate($record['message'], $record['context']);
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
    /**
     * Interpolates context values into the message placeholders.
     *
     * @author PHP Framework Interoperability Group
     *
     * @param string              $message
     * @param array<string,mixed> $context
     *
     * @return string
     */
    public function interpolate($message, $context)
    {
        // build a replacement array with braces around the context keys
        $replace = array();
        foreach ($context as $key => $val) {
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace[sprintf('{%s}', $key)] = $val;
            }
        }
        // interpolate replacement values into the message and return
        return strtr($message, $replace);
    }
}
