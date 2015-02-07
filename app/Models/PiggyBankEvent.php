<?php namespace FireflyIII\Models;

use Illuminate\Database\Eloquent\Model;

class PiggyBankEvent extends Model
{

    public function getDates()
    {
        return ['created_at', 'updated_at', 'date'];
    }

    public function piggyBank()
    {
        return $this->belongsTo('FireflyIII\Models\PiggyBank');
    }

    public function transactionJournal()
    {
        return $this->belongsTo('FireflyIII\Models\TransactionJournal');
    }

}
