<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MigrateOldMessagesSeeder extends Seeder
{
    public function run(): void
    {
        $oldConn = DB::connection('mysqlOld');
        $newConn = DB::connection();

        // Copy messages
        $oldMessages = $oldConn->table('messages')->orderBy('id')->get();
        if ($oldMessages->isNotEmpty()) {
            $rows = $oldMessages->map(function ($m) {
                return [
                    'id' => $m->id,
                    'sender' => $m->sender ?? null,
                    'receiver' => $m->receiver ?? null,
                    'subject' => $m->subject ?? null,
                    'message' => $m->message ?? null,
                    'date' => $m->date ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();

            // Use insert on chunks to avoid large single queries
            foreach (array_chunk($rows, 500) as $chunk) {
                $newConn->table('messages')->insert($chunk);
            }

            // Set messages auto-increment to max(id)+1 to avoid conflict with preserved ids
            $maxId = $oldMessages->max('id');
            if ($maxId !== null) {
                $driver = $newConn->getDriverName();
                // Only adjust for MySQL-compatible drivers
                if (in_array($driver, ['mysql', 'pdo_mysql'], true)) {
                    $newConn->statement('ALTER TABLE `messages` AUTO_INCREMENT = '.((int) $maxId + 1));
                }
            }
        }

        // Copy messagesRead -> messages_read
        $oldRead = $oldConn->table('messagesRead')->get();
        if ($oldRead->isNotEmpty()) {
            $rows = $oldRead->map(function ($r) {
                return [
                    'id' => $r->id,
                    'receiver' => $r->receiver ?? null,
                    'read_at' => now(),
                ];
            })->toArray();

            foreach (array_chunk($rows, 500) as $chunk) {
                // ignore duplicates if any
                foreach ($chunk as $row) {
                    try {
                        $newConn->table('messages_read')->insert($row);
                    } catch (\Exception $e) {
                        // ignore duplicate key or other insert errors for safety during seeding
                    }
                }
            }
        }

        // Copy messagesDeleted -> messages_deleted
        $oldDeleted = $oldConn->table('messagesDeleted')->get();
        if ($oldDeleted->isNotEmpty()) {
            $rows = $oldDeleted->map(function ($r) {
                return [
                    'id' => $r->id,
                    'receiver' => $r->receiver ?? null,
                    'deleted_at' => now(),
                ];
            })->toArray();

            foreach (array_chunk($rows, 500) as $chunk) {
                foreach ($chunk as $row) {
                    try {
                        $newConn->table('messages_deleted')->insert($row);
                    } catch (\Exception $e) {
                        // ignore duplicates
                    }
                }
            }
        }
    }
}
