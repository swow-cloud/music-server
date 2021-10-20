<?php
/**
 * This file is part of SwowCloud
 * @license  https://github.com/swow-cloud/music-server/blob/main/LICENSE
 */

declare(strict_types=1);

namespace SwowCloud\MusicServer\Db\Pool;

use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;
use SwowCloud\MusicServer\Db\Exception\DriverNotFoundException;
use SwowCloud\MusicServer\Db\Exception\InvalidDriverException;

class PoolFactory
{
    /**
     * @var Pool[]
     */
    protected array $pools = [];

    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getPool(string $name): Pool
    {
        if (isset($this->pools[$name])) {
            return $this->pools[$name];
        }

        $config = $this->container->get(ConfigInterface::class);
        $driver = $config->get(sprintf('db.%s.driver', $name), 'pdo');
        $class = $this->getPoolName($driver);

        $pool = make($class, [$this->container, $name]);
        if (!$pool instanceof Pool) {
            throw new InvalidDriverException(sprintf('Driver %s is not invalid.', $driver));
        }

        return $this->pools[$name] = $pool;
    }

    protected function getPoolName(string $driver): ?string
    {
        switch (strtolower($driver)) {
            case 'mysql':
                return MySQLPool::class;
            case 'pdo':
                return PDOPool::class;
        }

        if (class_exists($driver)) {
            return $driver;
        }

        throw new DriverNotFoundException(sprintf('Driver %s is not found.', $driver));
    }
}
