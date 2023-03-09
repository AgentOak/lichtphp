<?php
declare(strict_types=1);

namespace LichtPHP\Config;

use DomainException;
use LengthException;
use LogicException;

class INIConfiguration implements Configuration {
    /**
     * @param array<string, string|array<string, string>> $options
     */
    public function __construct(
        private readonly array $options,
        private readonly string $sectionName
    ) {
    }

    public function section(string $name): Configuration {
        if ($this->sectionName !== "") {
            throw new LogicException("INIConfigurations cannot have subsections");
        }

        if (!array_key_exists($name, $this->options) || !is_array($this->options[$name])) {
            throw new ConfigurationException("Configuration is missing section '$name'");
        }

        return new INIConfiguration($this->options[$name], "[$name]");
    }

    /**
     * @template TAllowBlank of bool
     * @param literal-string $option
     * @param TAllowBlank $allowBlank
     * @return (TAllowBlank is true ? string : non-empty-string)
     */
    public function getString(string $option, bool $allowBlank = false): string {
        if (strpbrk($option, ";=") !== false) {
            throw new DomainException("INIConfigurations may not have ';' or '=' in their key names");
        } elseif ($option === "") {
            throw new LengthException("INIConfiguration may not have empty key names");
        }

        if (!array_key_exists($option, $this->options) || !is_string($this->options[$option])) {
            throw new ConfigurationException("Configuration is missing key '{$this->sectionName}$option'");
        }

        $value = trim($this->options[$option]);
        if (!$allowBlank && $value === "") {
            throw new ConfigurationException("Configuration key '{$this->sectionName}$option' may not be blank");
        }

        return $value;
    }

    public function getBool(string $option): bool {
        $value = filter_var($this->getString($option), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if ($value === null) {
            throw new ConfigurationException("Configuration key '{$this->sectionName}$option' is not a boolean");
        }

        return $value;
    }

    /**
     * @template TMin of int
     * @template TMax of int
     * @param literal-string $option
     * @param TMin $min
     * @param TMax $max
     * @return (TMin is int<0, max> ? (TMin is positive-int ? positive-int : int<0, max>)
     *         : (TMax is int<min, 0> ? (TMax is negative-int ? negative-int : int<min, 0>) : int))
     */
    public function getInt(string $option, int $min = PHP_INT_MIN, int $max = PHP_INT_MAX): int {
        if ($min > $max) {
            throw new DomainException("min value may not be greater than max value");
        }

        $value = filter_var($this->getString($option), FILTER_VALIDATE_INT);

        if ($value === false) {
            throw new ConfigurationException("Configuration key '{$this->sectionName}$option' is not an integer");
        } elseif ($value < $min || $value > $max) {
            throw new ConfigurationException("Configuration key '{$this->sectionName}$option' integer is out of "
                . "allowed range [$min,$max]");
        }

        return $value;
    }

    public function getFloat(string $option): float {
        $value = filter_var($this->getString($option), FILTER_VALIDATE_FLOAT);

        if ($value === false) {
            throw new ConfigurationException("Configuration key '{$this->sectionName}$option' is not a float");
        }

        return $value;
    }

    /**
     * Loads and parses the given ini file into a Configuration object.
     *
     * @param non-empty-string $filename the ini file to load
     * @return Configuration representing the ini file.
     *
     * @throws ConfigurationException when the given file could not be parsed as ini.
     */
    public static function fromFile(string $filename): Configuration {
        $options = parse_ini_file($filename, process_sections: true);
        if ($options === false) {
            throw new ConfigurationException("Parsing configuration file '$filename' failed");
        }
        return new INIConfiguration($options, "");
    }
}
