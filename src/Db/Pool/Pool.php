<?php
/**
 * This file is part of SwowCloud
 * @license  https://github.com/swow-cloud/music-server/blob/main/LICENSE
 */

declare(strict_types=1);

namespace SwowCloud\WebSocket\Db\Pool;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Pool\Pool as HyperfPool;
use Hyperf\Utils\Arr;
use Psr\Container\ContainerInterface;
use SwowCloud\WebSocket\Db\Frequency;

abstract class Pool extends HyperfPool
{
    protected string $name;

    protected array $config;

    public function __construct(ContainerInterface $container, string $name)
    {
        $config = $container->get(ConfigInterface::class);
        $key = sprintf('db.%s', $name);
        if (!$config->has($key)) {
            throw new \InvalidArgumentException(sprintf('config[%s] is not exist!', $key));
        }

        $this->name = $name;
        $this->config = $config->get($key);
        $options = Arr::get($this->config, 'pool', []);
        $this->frequency = make(Frequency::class, [$this]);

        parent::__construct($container, $options);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getConfig(): array
    {
        return $this->config;
    }
}
