<?php
/**
 * Created by PhpStorm.
 * User: Gumacs
 * Date: 2016-12-20
 * Time: 12:56 PM
 */

namespace Loggers;


use Carbon\Carbon;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

class FileLogger extends AbstractLogger
{
    protected $level;
    protected $threshold;
    protected $path;
    protected $lastLine;

    protected $logLevels = array(
        LogLevel::EMERGENCY => 0,
        LogLevel::ALERT => 1,
        LogLevel::CRITICAL => 2,
        LogLevel::ERROR => 3,
        LogLevel::WARNING => 4,
        LogLevel::NOTICE => 5,
        LogLevel::INFO => 6,
        LogLevel::DEBUG => 7
    );

    protected $options = [
        'defaultPath' => '',
        'fileNameFormat' => false,
        'prefix' => 'log_',
        'fileDateFormat' => 'Y-m-d',
        'extension' => 'txt',

        'level' => LogLevel::INFO,
        'logDateFormat' => 'Y-m-d H:i:s.u',
        'logFormat' => false,
    ];

    public function __construct($path, $threshold, $options = [])
    {
        $this->threshold = $this->logLevels[$threshold];
        $this->path = $path;

        $this->options['defaultPath'] = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'logs';

        foreach ($options as $k => $v) {
            $options[$k] = $v;
        }
    }

    public function generateFilename()
    {
        if (!$this->options['fileNameFormat']) {
            $name = Carbon::now()->format($this->options['fileDateFormat']) . "_{$this->options['level']}";
            $file_name = "{$this->options['prefix']}{$name}.{$this->options['extension']}";
            return $file_name;
        } else {
            $file_name = $this->createFileNameFromFormat();
            return $file_name;
        }
    }

    public function createFileNameFromFormat()
    {
        $format = $this->options['fileNameFormat'];

        foreach ($this->options as $k => $v) {
            if (is_string($v) && $k !== 'extension') {
                $pattern = "{{$k}}";
                $format = str_replace($pattern, $v, $format);
            }
        }

        $file_name = "$format.{$this->options['extension']}";

        return $format;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function log($level, $message, array $context = array())
    {
        $this->options['level'] = $level;
        if ($this->logLevels[$level] > $this->threshold) {
            return;
        }

        if (!is_string($message) && !(is_object($message) && method_exists($message, '__toString'))) {
            return;
        }

        $file_name = $this->generateFilename();

        $this->write($file_name, $message, $context);
    }

    public function write($file_name, $message, $context)
    {
        $message = $this->replacePlaceholders($message, $context);

        //TODO logformat part
        $messagerow = $this->createLogRow($message);

        $file = $this->path . DIRECTORY_SEPARATOR . $file_name;
        file_put_contents($file, $messagerow, FILE_APPEND);
    }

    public function replacePlaceholders($message, $context)
    {
        foreach ($context as $k => $v) {
            $placeholder = '{' . $k . '}';
            if ($k === 'exception' && $v instanceof \Exception) {
                $text = "Exception found at {$v->getFile()} {$v->getLine()} with code {$v->getCode()}: {$v->getMessage()}";
                $message = str_replace($placeholder, $text, $message);
            } elseif (is_string($v) || method_exists($v, '__toString')) {
                $message = str_replace($placeholder, $v, $message);
            } else {
                return false;
            }
        }

        return $message;
    }

    public function createLogRow($message)
    {
        $time = Carbon::now()->format($this->options['logDateFormat']);
        $row = "[{$time}] -- Log level: {$this->options['level']} -- Message: {$message} \n";

        $this->lastLine = $row;
        return $row;
    }

    /**
     * @return string
     */
    public function getLastLine()
    {
        return $this->lastLine;
    }


}