<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\AutoUpgrade\Log;

/**
 * This class retrieves all message to display during the upgrade / rollback tasks.
 */
abstract class Logger implements LoggerInterface
{
    const DEBUG = 1;
    const INFO = 2;
    const NOTICE = 3;
    const WARNING = 4;
    const ERROR = 5;
    const CRITICAL = 6;
    const ALERT = 7;
    const EMERGENCY = 8;

    /**
     * @var string[]
     */
    public static $levels = [
        self::DEBUG => 'DEBUG',
        self::INFO => 'INFO',
        self::NOTICE => 'NOTICE',
        self::WARNING => 'WARNING',
        self::ERROR => 'ERROR',
        self::CRITICAL => 'CRITICAL',
        self::ALERT => 'ALERT',
        self::EMERGENCY => 'EMERGENCY',
    ];

    /**
     * @var resource|false|null File descriptor of the log file
     */
    protected $fd;

    /** @var array<string, string> */
    protected $sensitiveData = [];

    public function __destruct()
    {
        if (is_resource($this->fd)) {
            fclose($this->fd);
        }
    }

    /**
     * @param array<mixed> $context
     */
    public function alert(string $message, array $context = []): void
    {
        $this->log(self::ALERT, $message, $context);
    }

    /**
     * @param array<mixed> $context
     */
    public function critical(string $message, array $context = []): void
    {
        $this->log(self::CRITICAL, $message, $context);
    }

    /**
     * @param array<mixed> $context
     */
    public function debug(string $message, array $context = []): void
    {
        $this->log(self::DEBUG, $message, $context);
    }

    /**
     * @param array<mixed> $context
     */
    public function emergency(string $message, array $context = []): void
    {
        $this->log(self::EMERGENCY, $message, $context);
    }

    /**
     * @param array<mixed> $context
     */
    public function error(string $message, array $context = []): void
    {
        $this->log(self::ERROR, $message, $context);
    }

    /**
     * @param array<mixed> $context
     */
    public function info(string $message, array $context = []): void
    {
        $this->log(self::INFO, $message, $context);
    }

    /**
     * @param array<mixed> $context
     */
    public function notice(string $message, array $context = []): void
    {
        $this->log(self::NOTICE, $message, $context);
    }

    /**
     * @param array<mixed> $context
     */
    public function warning(string $message, array $context = []): void
    {
        $this->log(self::WARNING, $message, $context);
    }

    /**
     * Equivalent of the old $nextQuickInfo
     * Used during upgrade. Will be displayed in the lower panel.
     *
     * @return string[] Details on what happened during the execution. Verbose levels: DEBUG / INFO / WARNING
     */
    public function getLogs(): array
    {
        return [];
    }

    /**
     * Return the last message stored with the INFO level.
     * Equivalent of the old $next_desc
     * Used during upgrade. Will be displayed on the top left panel.
     */
    abstract public function getLastInfo(): ?string;

    public function updateLogsPath(string $logsPath): void
    {
        $this->fd = fopen($logsPath, 'a');
    }

    /**
     * {@inheritdoc}
     *
     * @param array<mixed> $context
     */
    public function log($level, string $message, array $context = []): void
    {
        $className = $this->getCallingClass();

        if (is_resource($this->fd)) {
            fwrite(
                $this->fd,
                '[' . date('Y-m-d H:i:s') . '] ' . self::$levels[$level] . ' - ' .
                ($className ? $className . ' - ' : '') .
                $message . PHP_EOL
            );
        }
    }

    /**
     * Get the name of the class that called the logger.
     */
    private function getCallingClass(): ?string
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 8);

        foreach ($trace as $frame) {
            if (
                isset($frame['class']) &&
                strpos($frame['class'], __NAMESPACE__) === false
            ) {
                // Extract the class name without the namespace
                return substr(strrchr($frame['class'], '\\') ?: $frame['class'], 1);
            }
        }

        return null;
    }

    /**
     * @param array<string, string> $sensitiveData List of data to change with another value
     */
    public function setSensitiveData(array $sensitiveData): self
    {
        $this->sensitiveData = $sensitiveData;

        return $this;
    }

    public function cleanFromSensitiveData(string $message): string
    {
        if (empty($this->sensitiveData)) {
            return $message;
        }

        return str_replace(
            array_keys($this->sensitiveData),
            array_values($this->sensitiveData),
            $message
        );
    }
}
