<?php
/**
 * Created by PhpStorm.
 * User: Gumacs
 * Date: 2016-12-21
 * Time: 10:14 PM
 */

namespace Tests\Unit;


use Carbon\Carbon;
use Loggers\FileLogger;
use Psr\Log\LogLevel;


class FileLoggerTest extends \PHPUnit_Framework_TestCase
{
    /* @var FileLogger $logger */
    private $logger;

    public function setUp()
    {
        $this->logger = new FileLogger(getcwd() .
            DIRECTORY_SEPARATOR . 'tests' .
            DIRECTORY_SEPARATOR . 'helpers', LogLevel::DEBUG);
    }

    public function testLog(){
        $e = new \Exception();
        $level = LogLevel::WARNING;
        $this->logger->log($level, "message {alma} {korte} {exception}", ["alma" => 'logtest1', "korte" => "logtest2", "exception" => $e]);

        $file_name = "log_" . Carbon::now()->format('Y-m-d') . "_{$level}.txt";
        $file = getcwd() .
            DIRECTORY_SEPARATOR . 'tests' .
            DIRECTORY_SEPARATOR . 'Helpers' .
            DIRECTORY_SEPARATOR . $file_name;
        $return = file_exists($file);

        $last_line = file($file);

        $this->assertEquals(true, $return);
        $this->assertEquals($this->logger->getLastLine(), $last_line[count($last_line)-1]);
    }

    public function testWrite()
    {
        $e = new \Exception();
        $file_name = $this->logger->generateFilename();

        $this->logger->write($file_name, "message {alma} {korte} {exception}", ["alma" => 'value1', "korte" => "value2", "exception" => $e]);

        $file = getcwd() .
            DIRECTORY_SEPARATOR . 'tests' .
            DIRECTORY_SEPARATOR . 'Helpers' .
            DIRECTORY_SEPARATOR . $file_name;
        $return = file_exists($file);

        $last_line = file($file);

        $this->assertEquals(true, $return);
        $this->assertEquals($this->logger->getLastLine(), $last_line[count($last_line)-1]);
    }

    public function testReplacePlaceholders()
    {
        $e = new \Exception();
        $message = $this->logger->replacePlaceholders("message {alma} {korte} {exception}", ["alma" => 'value1', "korte" => "value2", "exception" => $e]);

        $expected = "message value1 value2 Exception found at {$e->getFile()} {$e->getLine()} with code {$e->getCode()}: {$e->getMessage()}";

        $this->assertEquals($expected, $message);
    }

    public function testGenerateFileName()
    {
        $name = $this->logger->generateFilename();

        $expected = "log_" . Carbon::now()->format('Y-m-d') . "_info.txt";

        $this->assertEquals($expected, $name);
    }

    public function testCreateLogRow()
    {
        $message = $this->logger->createLogRow("message");

        $time = Carbon::now()->format("Y-m-d H:i:s.u");
        $expected = "[{$time}] -- Log level: info -- Message: message \n";

        $this->assertEquals($expected, $message);
    }
}
