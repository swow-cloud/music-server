<?php
/**
 * This file is part of SwowCloud
 * @license  https://github.com/swow-cloud/music-server/blob/main/LICENSE
 */

declare(strict_types=1);

namespace SwowCloud\MusicServer\Config;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Utils\Arr;

class Config implements ConfigInterface
{
    private array $configs;

    public function __construct(array $configs)
    {
        $this->configs = $configs;
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $key identifier of the entry to look for
     * @param null|mixed $default default value of the entry when does not found
     *
     * @return mixed entry
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return data_get($this->configs, $key, $default);
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * @param string $key identifier of the entry to look for
     */
    public function has(string $key): bool
    {
        return Arr::has($this->configs, $key);
    }

    /**
     * Set a value to the container by its identifier.
     *
     * @param string $key identifier of the entry to set
     * @param mixed $value the value that save to container
     */
    public function set(string $key, mixed $value = null): void
    {
        data_set($this->configs, $key, $value);
    }
}
