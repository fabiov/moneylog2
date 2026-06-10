<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Helpers\Type;
use Illuminate\Console\Command;
use Spatie\DbDumper\Compressors\Bzip2Compressor;
use Spatie\DbDumper\Databases\MySql;
use Spatie\DbDumper\Exceptions\CannotSetParameter;

class DbDump extends Command
{
    /** @var string */
    protected $signature = 'app:db-dump';

    /** @var string */
    protected $description = 'Make a DB dump adn upload it on dropbox';

    /**
     * @throws CannotSetParameter
     */
    public function handle(): int
    {
        $dumpDir = storage_path('dumps');
        if (! file_exists($dumpDir)) {
            mkdir($dumpDir, 0755, true);
        }
        $filePath = $dumpDir . '/' . 'moneylog2_' . date('Y-m-d.H.i') . '.sql.bz2';

        MySql::create()
            ->setDbName(Type::string(config('database.connections.mysql.database')))
            ->setUserName(Type::string(config('database.connections.mysql.username')))
            ->setPassword(Type::string(config('database.connections.mysql.password')))
            ->setHost(Type::string(config('database.connections.mysql.host')))
            ->useCompressor(new Bzip2Compressor)
            ->dumpToFile($filePath);

        $this->info("Created file dump $filePath");

        return self::SUCCESS;
    }
}
