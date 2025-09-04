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
    // disable query log to avoid memory growth while processing large datasets
    DB::disableQueryLog();

        // Copy messages in chunks to avoid loading the entire table into memory
        $maxId = null;
        $oldConn->table('messages')->orderBy('id')->chunkById(500, function ($chunk) use ($newConn, &$maxId) {
            $rows = [];
            foreach ($chunk as $m) {
                $rows[] = [
                    'id' => $m->id,
                    'sender' => $m->sender ?? null,
                    'receiver' => $m->receiver ?? null,
                    'subject' => $m->subject ?? null,
                    'message' => $m->message ?? null,
                    'date' => $m->date ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $maxId = $m->id > ($maxId ?? 0) ? $m->id : $maxId;
            }

            if (!empty($rows)) {
                foreach (array_chunk($rows, 500) as $insertChunk) {
                    $newConn->table('messages')->insert($insertChunk);
                }
            }
        });

        // Set messages auto-increment to max(id)+1 to avoid conflict with preserved ids
        if ($maxId !== null) {
            $driver = $newConn->getDriverName();
            // Only adjust for MySQL-compatible drivers
            if (in_array($driver, ['mysql', 'pdo_mysql'], true)) {
                $newConn->statement('ALTER TABLE `messages` AUTO_INCREMENT = '.((int) $maxId + 1));
            }
        }

        // Copy messagesRead -> messages_read
        // Copy messagesRead -> messages_read using chunking
        $oldConn->table('messagesRead')->orderBy('id')->chunkById(500, function ($chunk) use ($newConn) {
            foreach ($chunk as $r) {
                $row = [
                    'id' => $r->id,
                    'receiver' => $r->receiver ?? null,
                    'read_at' => now(),
                ];
                try {
                    $newConn->table('messages_read')->insert($row);
                } catch (\Exception $e) {
                    // ignore duplicate key or other insert errors for safety during seeding
                }
            }
        });

        // Copy messagesDeleted -> messages_deleted
        // Copy messagesDeleted -> messages_deleted using chunking
        $oldConn->table('messagesDeleted')->orderBy('id')->chunkById(500, function ($chunk) use ($newConn) {
            foreach ($chunk as $r) {
                $row = [
                    'id' => $r->id,
                    'receiver' => $r->receiver ?? null,
                    'deleted_at' => now(),
                ];
                try {
                    $newConn->table('messages_deleted')->insert($row);
                } catch (\Exception $e) {
                    // ignore duplicates
                }
            }
        });
    }
}
