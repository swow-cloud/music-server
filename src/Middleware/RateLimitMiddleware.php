<?php
/**
 * This file is part of SwowCloud
 * @license  https://github.com/swow-cloud/music-server/blob/main/LICENSE
 */

declare(strict_types=1);

namespace SwowCloud\WebSocket\Middleware;

use Psr\Http\Message\RequestInterface;
use Swow\Http\Status;
use SwowCloud\RateLimit\Exception\LimitExceeded;
use SwowCloud\RateLimit\Rate;
use SwowCloud\RateLimit\RedisRateLimiter;
use Swow\Http\Server\Connection;
use SwowCloud\WebSocket\Redis\RedisFactory;
use SwowCloud\WebSocket\WebSocket\Middleware\MiddlewareInterface;
use Swow\Http\Exception as HttpException;

class RateLimitMiddleware implements MiddlewareInterface
{
    public function __construct(RedisFactory $factory)
    {
        $this->factory = $factory;
        $this->key = 'swow-cloud:redis-rateLimiter';
        $this->operations = config('rate_limit.operations');
        $this->interval = config('rate_limit.interval');
    }

    /**
     * @throws \SwowCloud\RateLimit\Exception\LimitExceeded
     */
    public function process(RequestInterface $request, Connection $connection): void
    {
        /**
         * @var \Redis $redis
         */
        $redis = $this->factory->get('default');
        try {
            $rateLimiter = new RedisRateLimiter(Rate::custom($this->operations, $this->interval), $redis);
            $rateLimiter->limit($this->key);
        }catch (LimitExceeded){
            throw new HttpException(Status::TOO_MANY_REQUESTS,'WebSocket connection limit exceeded');
        }

    }
}
