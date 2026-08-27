<?php

declare(strict_types=1);

namespace Icepay\Payment\Test\Fakes;

use Magento\Framework\App\CacheInterface;

class FakeCache implements CacheInterface
{
    private array $entries = [];
    private array $lifetimes = [];

    public function withEntry(string $identifier, string $value): self
    {
        $this->entries[$identifier] = $value;

        return $this;
    }

    public function getFrontend()
    {
        throw new \BadMethodCallException('The fake cache has no frontend');
    }

    public function load($identifier)
    {
        return $this->entries[$identifier] ?? false;
    }

    public function save($data, $identifier, $tags = [], $lifeTime = null)
    {
        $this->entries[$identifier] = $data;
        $this->lifetimes[$identifier] = $lifeTime;

        return true;
    }

    public function remove($identifier)
    {
        unset($this->entries[$identifier], $this->lifetimes[$identifier]);

        return true;
    }

    public function clean($tags = [])
    {
        $this->entries = [];
        $this->lifetimes = [];

        return true;
    }

    public function storedValue(string $identifier): ?string
    {
        return $this->entries[$identifier] ?? null;
    }

    public function storedLifetime(string $identifier): ?int
    {
        return $this->lifetimes[$identifier] ?? null;
    }
}
