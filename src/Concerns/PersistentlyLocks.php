<?php

declare(strict_types=1);

namespace Mpyw\LaravelDatabaseAdvisoryLock\Concerns;

use Mpyw\LaravelDatabaseAdvisoryLock\Contracts\LockConflictException;
use Mpyw\LaravelDatabaseAdvisoryLock\Contracts\PersistentLock;

/**
 * @internal
 */
trait PersistentlyLocks
{
    abstract public function acquireOrFail(string $key, int $timeout = 0): PersistentLock;

    public function withLocking(string $key, callable $callback, int $timeout = 0): mixed
    {
        $lock = $this->acquireOrFail($key, $timeout);

        try {
            return $callback();
        } finally {
            $lock->release();
        }
    }

    public function acquire(string $key, int $timeout = 0): ?PersistentLock
    {
        try {
            return $this->acquire($key, $timeout);
        } catch (LockConflictException) {
            return null;
        }
    }
}
