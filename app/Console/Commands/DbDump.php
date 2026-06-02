<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Helpers\Type;
use Illuminate\Console\Command;
use Spatie\DbDumper\Compressors\GzipCompressor;
use Spatie\DbDumper\Databases\MySql;

class DbDump extends Command
{
    /** @var string */
    protected $signature = 'app:db-dump {directory}';

    /** @var string */
    protected $description = 'Make a DB dump';

    public function handle(): int
    {
        $directory = Type::string($this->argument('directory'));

        if (! is_dir($directory)) {
            if (! mkdir($directory, 0755, true) && ! is_dir($directory)) {
                $this->error("Directory could not be created: $directory");

                return self::FAILURE;
            }
        }

        $filePath = rtrim($directory, '/') . '/' . sprintf('moneylog2_%s.sql.gz', date('Y-m-d_H.i.s'));
        if ($this->verbosity) {
            $this->info("Creating dump file $filePath");
        }

        MySql::create()
            ->setDbName(Type::string(config('database.connections.mysql.database')))
            ->setUserName(Type::string(config('database.connections.mysql.username')))
            ->setPassword(Type::string(config('database.connections.mysql.password')))
            ->setHost(Type::string(config('database.connections.mysql.host')))
            ->useCompressor(new GzipCompressor)
            ->dumpToFile($filePath);

        return self::SUCCESS;
    }
}
