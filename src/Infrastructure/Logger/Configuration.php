<?php

namespace Logeecom\Infrastructure\Logger;

use Logeecom\Infrastructure\Configuration as ConfigInterface;
use Logeecom\Infrastructure\ServiceRegister;

/**
 * Class Configuration.
 *
 * @package Logeecom\Infrastructure\Logger
 */
class Configuration
{
    /**
     * Default minimum level for logging.
     */
    const DEFAULT_MIN_LOG_LEVEL = Logger::DEBUG;
    /**
     * Identifies if default logger should be used by default.
     */
    const DEFAULT_IS_DEFAULT_LOGGER_ENABLED = false;
    /**
     * Singleton instance.
     *
     * @var self
     */
    private static $instance;
    /**
     * Whether default logger is enabled or not.
     *
     * @var boolean
     */
    private $isDefaultLoggerEnabled;
    /**
     * Configuration service instance.
     *
     * @var ConfigInterface
     */
    private $shopConfiguration;
    /**
     * Minimum log level set.
     *
     * @var int
     */
    private $minLogLevel;
    /**
     * Integration name.
     *
     * @var string
     */
    private $integrationName;

    /**
     * Getting logger configuration instance
     *
     * @return Configuration
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    /**
     * Resets singleton instance. Required for proper tests.
     */
    public static function resetInstance()
    {
        self::$instance = null;
    }

    /**
     * Set default logger status (turning on/off)
     *
     * @param boolean $status
     *
     */
    public static function setDefaultLoggerEnabled($status)
    {
        self::getInstance()->setIsDefaultLoggerEnabled($status);
    }

    /**
     * Return whether default logger is enabled or not.
     *
     * @return bool
     *   Logger status true => enabled, false => disabled.
     */
    public function isDefaultLoggerEnabled()
    {
        if (empty($this->isDefaultLoggerEnabled)) {
            try {
                $this->isDefaultLoggerEnabled = $this->getShopConfiguration()->isDefaultLoggerEnabled();
            } catch (\Exception $ex) {
                // Catch if configuration is not set properly and for some reason throws exception
                // e.g. Client is still not authorized (meaning that configuration is not set)
                // and we want to log something
            }
        }

        return !empty($this->isDefaultLoggerEnabled) ? $this->isDefaultLoggerEnabled
            : self::DEFAULT_IS_DEFAULT_LOGGER_ENABLED;
    }

    /**
     * Set default logger status (enabled or disabled).
     *
     * @param bool $loggerStatus Logger status true => enabled, false => disabled.
     *
     */
    public function setIsDefaultLoggerEnabled($loggerStatus)
    {
        $this->getShopConfiguration()->setDefaultLoggerEnabled($loggerStatus);
        $this->isDefaultLoggerEnabled = $loggerStatus;
    }

    /**
     * Retrieves minimum log level set.
     *
     * @return int
     *   Log level:
     *    - error => 0
     *    - warning => 1
     *    - info => 2
     *    - debug => 3
     */
    public function getMinLogLevel()
    {
        if ($this->minLogLevel === null) {
            try {
                $this->minLogLevel = $this->getShopConfiguration()->getMinLogLevel();
            } catch (\Exception $ex) {
                // Catch if configuration is not set properly and for some reason throws exception
                // e.g. Client is still not authorized (meaning that configuration is not set)
                // and we want to log something
            }
        }

        return $this->minLogLevel !== null ? $this->minLogLevel : self::DEFAULT_MIN_LOG_LEVEL;
    }

    /**
     * Saves min log level in integration.
     *
     * @param int $minLogLevel Log level.
     *
     */
    public function setMinLogLevel($minLogLevel)
    {
        $this->getShopConfiguration()->saveMinLogLevel($minLogLevel);
        $this->minLogLevel = $minLogLevel;
    }

    /**
     * Retrieves integration name.
     *
     * @return string Integration name.
     */
    public function getIntegrationName()
    {
        if (empty($this->integrationName)) {
            try {
                $this->integrationName = $this->getShopConfiguration()->getIntegrationName();
            } catch (\Exception $ex) {
                // Catch if configuration is not set properly and for some reason throws exception
                // e.g. Client is still not authorized (meaning that configuration is not set)
                // and we want to log something
            }
        }

        return !empty($this->integrationName) ? $this->integrationName : 'unknown';
    }

    /**
     * Gets instance of configuration service.
     *
     * @return ConfigInterface Instance of configuration service.
     *
     */
    private function getShopConfiguration()
    {
        if ($this->shopConfiguration === null) {
            $this->shopConfiguration = ServiceRegister::getService(ConfigInterface::CLASS_NAME);
        }

        return $this->shopConfiguration;
    }
}
