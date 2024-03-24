<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseManager;

class Migration extends Command
{
    protected $signature = 'moneylog:migration';

    protected $description = 'Data migration';

    private Connection $old;

    private Connection $new;

    public function __construct(DatabaseManager $dbManager)
    {
        parent::__construct();

        $this->old = $dbManager->connection('old');
        $this->new = $dbManager->connection('mysql');
    }

    public function handle(): int
    {
        // db clean
        $this->new->delete('DELETE FROM movements');
        $this->new->delete('DELETE FROM accounts');
        $this->new->delete('DELETE FROM categories');
        $this->new->delete('DELETE FROM provisions');
        $this->new->delete('DELETE FROM settings');
        $this->new->delete('DELETE FROM users');

        // populate database
        $this->info(sprintf('%d users imported', $this->usersMigration()));
        $this->info(sprintf('%d settings imported', $this->settingsMigration()));
        $this->info(sprintf('%d accounts imported', $this->accountsMigration()));
        $this->info(sprintf('%d categories imported', $this->categoriesMigration()));
        $this->info(sprintf('%d movements imported', $this->movementsMigration()));
        $this->info(sprintf('%d provisions imported', $this->provisionsMigration()));

        return 0;
    }

    private function usersMigration(): int
    {
        $i = 0;
        $select = 'SELECT * FROM user WHERE id IN (1, 21)';
        $insert = 'INSERT INTO users (id, name, email, password, created_at) VALUES (?, ?, ?, ?, ?)';

        foreach ($this->old->select($select) as $row) {
            $this->new->insert($insert, [
                $row->id,
                $row->name,
                $row->email,
                '',
                date('Y-m-d H:i:s'),
            ]);

            $i++;
        }

        return $i;
    }

    private function settingsMigration(): int
    {
        $i = 0;
        $select = 'SELECT * FROM setting WHERE userId IN (1, 21)';
        $insert = 'INSERT INTO settings (id, payday, months, provisioning) values (?, ?, ?, ?)';

        foreach ($this->old->select($select) as $row) {
            $this->new->insert($insert, [
                $row->userId,
                $row->paypay,
                $row->months,
                $row->provisioning,
            ]);
            $i++;
        }

        return $i;
    }

    private function accountsMigration(): int
    {
        $i = 0;
        $select = 'SELECT * FROM account WHERE userId IN (1, 21)';
        $insert = 'INSERT INTO accounts (id, user_id, name, status, created_at) VALUES (?, ?, ?, ?, ?)';

        foreach ($this->old->select($select) as $row) {
            $this->new->insert($insert, [
                $row->id,
                $row->userId,
                $row->name,
                $row->status,
                date('Y-m-d H:i:s'),
            ]);
            $i++;
        }

        return $i;
    }

    private function movementsMigration(): int
    {
        $i = 0;
        $select = 'SELECT * FROM movement WHERE accountId IN (SELECT id FROM account WHERE userId IN (1, 21))';
        $insert = 'INSERT INTO movements (id, account_id, category_id, date, amount, description, created_at) '
                . 'VALUES (?, ?, ?, ?, ?, ?, ?)';

        foreach ($this->old->select($select) as $row) {
            $this->new->insert($insert, [
                $row->id,
                $row->accountId,
                $row->categoryId,
                $row->date,
                $row->amount,
                $row->description,
                $row->createdAt,
            ]);
            $i++;
        }

        return $i;
    }

    private function provisionsMigration(): int
    {
        $i = 0;
        $select = 'SELECT * FROM provision WHERE userId IN (1, 21)';
        $insert = 'INSERT INTO provisions (id, user_id, date, amount, description, created_at) '
                . 'VALUES (?, ?, ?, ?, ?, ?)';

        foreach ($this->old->select($select) as $row) {
            $this->new->insert($insert, [
                $row->id,
                $row->userId,
                $row->date,
                $row->amount,
                $row->description,
                date('Y-m-d  H:i:s'),
            ]);
            $i++;
        }

        return $i;
    }

    private function categoriesMigration(): int
    {
        $i = 0;
        $select = 'SELECT * FROM category WHERE userId IN (1, 21)';
        $insert = 'INSERT INTO categories (id, user_id, name, active, created_at) values (?, ?, ?, ?, ?)';

        foreach ($this->old->select($select) as $row) {
            $this->new->insert($insert, [
                $row->id,
                $row->userId,
                $row->description,
                $row->active,
                date('Y-m-d H:i:s'),
            ]);
            $i++;
        }

        return $i;
    }
}
