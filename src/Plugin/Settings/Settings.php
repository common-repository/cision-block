<?php

namespace CisionBlock\Plugin\Settings;

class Settings implements \IteratorAggregate, \Countable, \ArrayAccess
{
    /**
     * Name of configuration option.
     *
     * @var string
     */
    protected string $optionName = '';

    /**
     * An array of settings.
     *
     * @var array
     */
    protected array $settings = [];

    /**
     * Version
     *
     * @var string
     */
    public string $version = '1.0.7';

    /**
     * Settings constructor.
     *
     * @param string $optionName
     */
    public function __construct(string $optionName)
    {
        $this->optionName = $optionName;
        $this->load();
    }

    /**
     * @return array
     */
    public function toOptionsArray(): array
    {
        return $this->settings;
    }

    /**
     * @return false|string
     */
    public function toJSON()
    {
        return function_exists('json_encode') ? json_encode($this->toOptionsArray(), JSON_PRETTY_PRINT) : '';
    }

    /**
     * @return string
     */
    public function toYaml(): string
    {
        return function_exists('yaml_emit') ? yaml_emit($this->settings) : '';
    }

    /**
     * Returns the name of this option.
     *
     * @return string
     */
    public function getOptionName(): string
    {
        return $this->optionName;
    }

    /**
     * Returns the current version.
     *
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Delete this setting from database.
     * @return $this
     */
    public function delete(): self
    {
        delete_option($this->optionName);
        $this->settings = [];
        return $this;
    }

    /**
     * Sets a configuration value.
     *
     * @param string $name
     *   Name of option to set.
     *
     * @param mixed $value
     *   The value to set.
     */
    public function __set(string $name, $value)
    {
        $this->set($name, $value);
    }

    /**
     * Sets a configuration value.
     *
     * @param string $name
     *   Name of option to set.
     * @param mixed $value
     *   The value to set.
     * @return $this;
     */
    public function set(string $name, $value): self
    {
        $this->settings[$name] = $value;
        return $this;
    }

    /**
     * Sets configuration from array.
     *
     * @param array $settings
     * @return $this
     */
    public function setFromArray(array $settings): self
    {
        foreach ($settings as $key => $value) {
            $this->set($key, $value);
        }
        return $this;
    }

    /**
     * Check if a setting isset.
     *
     * @param string $key
     * @return bool
     */
    public function hasKey(string $key): bool
    {
        return $this->__isset($key);
    }

    /**
     * Check if a setting isset.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return $this->__isset($key);
    }

    /**
     * Check if a setting isset.
     *
     * @param string $key
     * @return bool
     */
    public function __isset(string $key)
    {
        return isset($this->settings[$key]);
    }

    /**
     * Add a setting.
     *
     * @param string $name
     *   Name of setting to add.
     * @param mixed $value
     *   Value to add.
     * @return $this
     */
    public function add(string $name, $value): self
    {
        if (!isset($this->settings[$name])) {
            $this->set($name, $value);
        }
        return $this;
    }

    /**
     * Get a configuration value.
     *
     * @param string $name
     *   Name of option to get.
     *
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->get($name);
    }

    /**
     * Get a configuration value.
     *
     * @param string $name
     *   Name of option to get.
     * @param mixed $default
     *   Default value to return if settings does not exist.
     *
     * @return mixed
     */
    public function get(string $name, $default = null)
    {
        return $this->settings[$name] ?? $default;
    }

    /**
     * Get a configuration value from array.
     *
     * @param string $name
     * @param int $index
     * @return mixed
     */
    public function getFromArray(string $name, int $index)
    {
        $value = $this->get($name);
        if (is_array($value) && count($value) >= $index) {
            return $value[$index];
        }
    }

    /**
     * Remove setting.
     *
     * @param string $name
     *   Name of setting to remove.
     */
    public function __unset(string $name)
    {
        $this->remove($name);
    }

    /**
     * Remove setting.
     *
     * @param string $name
     *   Name of setting to remove.
     * @return $this
     */
    public function remove(string $name): self
    {
        unset($this->settings[$name]);
        return $this;
    }

    /**
     * Rename setting.
     *
     * @param string $from
     *   Name of setting.
     * @param string $to
     *   New name for setting.
     * @return $this
     */
    public function rename(string $from, string $to): self
    {
        if (array_key_exists($from, $this->settings)) {
            $this->settings[$to] = $this->settings[$from];
            $this->remove($from);
        }
        return $this;
    }

    /**
     * Load settings from database.
     * @return $this
     */
    public function load(): self
    {
        $this->settings = get_option($this->optionName, []);
        return $this;
    }

    /**
     * Save setting to database.
     *
     * @return bool
     */
    public function save(): bool
    {
        ksort($this->settings);
        return update_option($this->optionName, $this->settings);
    }

    /**
     * Save settings to file.
     *
     * @param string $filename
     * @param string $format
     * @return false|int
     */
    public function saveToFile(string $filename, string $format = 'json')
    {
        $content = '';
        switch ($format) {
            case 'raw':
                $content = serialize($this->settings);
                break;
            case 'json':
                $content = $this->toJSON();
                break;
            case 'yaml':
                $content = $this->toYaml();
                break;
        }
        return @file_put_contents($filename, $content);
    }

    /**
     * Removes any settings that is not defined in $options.
     *
     * @param array $options
     *   An array which keys will be used to validate the current settings keys.
     * @return $this
     */
    public function clean(array $options): self
    {
        foreach ($this->settings as $key => $value) {
            if (!in_array($key, $options)) {
                unset($this->settings[$key]);
            }
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->settings);
    }

    /**
     * @inheritdoc
     */
    public function count(): int
    {
        return (int) count($this->settings);
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset): bool
    {
        return $this->__isset($offset);
    }

    /**
     * @inheritdoc
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    /**
     * @inheritdoc
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        $this->__set($offset, $value);
    }

    /**
     * @inheritdoc
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }
}
