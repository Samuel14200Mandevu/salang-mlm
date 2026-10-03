<?php

namespace Tests;

use Tests\Concerns\UsesMysqlDatabase;

/**
 * Tests MLM nécessitant MySQL natif (FIND_IN_SET, etc.).
 * En local sans MySQL, les tests unitaires MLM passent via SqlDialect + SQLite.
 */
abstract class MysqlTestCase extends TestCase
{
    use UsesMysqlDatabase;

    protected function setUp(): void
    {
        if (env('MLM_TEST_USE_MYSQL', false)) {
            $this->configureMysqlConnection();
        }

        parent::setUp();
    }
}
