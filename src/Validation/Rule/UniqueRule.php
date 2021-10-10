<?php

namespace App\Validation\Rule;

use Doctrine\DBAL\Connection;
use Rakit\Validation\Rule;

class UniqueRule extends Rule
{
    protected $message = ":attribute :value has been used";

    protected $fillableParams = ['table', 'column', 'except'];

    public function __construct(
        private Connection $connection
    ) {
    }

    public function check($value): bool
    {
        $this->requireParameters(['table', 'column']);

        $column = $this->parameter('column');
        $table = $this->parameter('table');
        $except = $this->parameter('except');

        if ($except && $except == $value) {
            return true;
        }

        $result =
            $this->connection->createQueryBuilder()
                ->select('count(1)')
                ->from($table)
                ->where($column . ' = ?')
                ->setParameter(0, $value)
                ->executeQuery()->fetchOne();
        return !$result;
    }
}