<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Helpers\Type;
use Illuminate\Console\Command;
use Spatie\DbDumper\Compressors\GzipCompressor;
use Spatie\DbDumper\Databases\MySql;
use Spatie\DbDumper\Exceptions\CannotSetParameter;
use Spatie\Dropbox\Client;

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
        $filePath = tempnam(sys_get_temp_dir(), 'laravel_dump_');
        if ($this->verbosity) {
            $this->info("Creating temporary file $filePath");
        }

        MySql::create()
            ->setDbName(Type::string(config('database.connections.mysql.database')))
            ->setUserName(Type::string(config('database.connections.mysql.username')))
            ->setPassword(Type::string(config('database.connections.mysql.password')))
            ->setHost(Type::string(config('database.connections.mysql.host')))
            ->useCompressor(new GzipCompressor)
            ->dumpToFile($filePath);

        $resource = fopen($filePath, 'r');
        if (! $resource) {
            $this->error('Unable to create dump file.');

            return self::FAILURE;
        }

        $client = new Client(Type::string(config('app.dropbox_token')));
        try {
            $client->upload(sprintf('moneylog2_%s.sql.gz', date('Y-m-d_H')), $resource);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        unlink($filePath);

        return self::SUCCESS;
    }
}
