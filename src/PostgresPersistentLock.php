<?php

declare(strict_types=1);

namespace Mpyw\LaravelDatabaseAdvisoryLock;

use Illuminate\Database\PostgresConnection;
use Mpyw\LaravelDatabaseAdvisoryLock\Concerns\ReleasesWhenDestructed;
use Mpyw\LaravelDatabaseAdvisoryLock\Contracts\PersistentLock;
use WeakMap;

final class PostgresPersistentLock implements PersistentLock
{
    use ReleasesWhenDestructed;

    private bool $released = false;

    /**
     * @param WeakMap<PersistentLock, bool> $locks
     */
    public function __construct(
        private PostgresConnection $connection,
        private WeakMap $locks,
        private string $key,
    ) {
    }

    public function release(): bool
    {
        if (!$this->released) {
            $this->released = (new Selector($this->connection))
                ->selectBool('SELECT pg_advisory_unlock(hashtext(?))', [$this->key], false);

            if ($this->released) {
                $this->locks->offsetUnset($this);
            }
        }

        return $this->released;
    }
}
