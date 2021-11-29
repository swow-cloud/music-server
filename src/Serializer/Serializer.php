<?php
/**
 * This file is part of SwowCloud
 * @license  https://github.com/swow-cloud/music-server/blob/main/LICENSE
 */

declare(strict_types=1);

namespace SwowCloud\WsServer\Serializer;

use SwowCloud\Contract\SerializerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

abstract class Serializer implements SerializerInterface
{
    protected array $normalizer = [];

    protected array $encoders = [];

    protected \Symfony\Component\Serializer\Serializer $serializer;

    public function __construct(
        array $normalizers = [
            ObjectNormalizer::class,
        ],
        array $encoders = [
            JsonEncoder::class,
            XmlEncoder::class,
            YamlEncoder::class,
        ]
    ) {
        foreach ($normalizers as $normalizer) {
            $this->normalizer[] = new $normalizer();
        }
        foreach ($encoders as $encoder) {
            $this->encoders[] = new $encoder();
        }
        $this->serializer = new \Symfony\Component\Serializer\Serializer($this->normalizer, $this->encoders);
    }
}
