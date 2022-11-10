<?php
/*
 * This file is part of drupalindus
 */
namespace DgfipSI1\testLoggerTests;

use DgfipSI1\testLogger\LogTestCase;

/**
 * In order to test abstract class logTestCase we need a non abstract child
 *
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

class LogTestCaseConcrete extends LogTestCase
{

}
