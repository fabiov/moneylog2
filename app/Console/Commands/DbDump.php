<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Helpers\Type;
use Illuminate\Console\Command;
use Spatie\DbDumper\Compressors\GzipCompressor;
use Spatie\DbDumper\Databases\MySql;
use Spatie\DbDumper\Exceptions\CannotSetParameter;

class DbDump extends Command
{
    /** @var string */
    protected $signature = 'app:db-dump';

    /** @var string */
    protected $description = 'DB dump';

    /**
     * @throws CannotSetParameter
     */
    public function handle(): int
    {
        MySql::create()
            ->setDbName(Type::string(config('database.connections.mysql.database')))
            ->setUserName(Type::string(config('database.connections.mysql.username')))
            ->setPassword(Type::string(config('database.connections.mysql.password')))
            ->setHost(Type::string(config('database.connections.mysql.host')))
            ->useCompressor(new GzipCompressor)
            ->dumpToFile(storage_path(sprintf('app/dump/moneylog2_%s.sql.gz', date('Y-m-d_H'))));

        return self::SUCCESS;
    }
}
