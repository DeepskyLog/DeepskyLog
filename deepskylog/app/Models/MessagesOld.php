<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessagesOld extends Model
{
    protected $connection = 'mysqlOld';

    protected $table = 'messages';

    public static function getNumberOfUnreadMails($id): int
    {
        $allMails = MessagesOld::where('receiver', $id)->orWhere('receiver', 'all')->pluck('id');
        $deletedMails = MessagesDeletedOld::where('receiver', $id)->pluck('id');
        $readMails = MessagesReadOld::where('receiver', $id)->pluck('id');

        // Remove all ids from deletedMails from allMails
        $allMails = $allMails->diff($deletedMails)->diff($readMails);

        return $allMails->count();
    }
}
