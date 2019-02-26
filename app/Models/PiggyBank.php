<?php
/**
 * PiggyBank.php
 * Copyright (c) 2017 thegrumpydictator@gmail.com
 *
 * This file is part of Firefly III.
 *
 * Firefly III is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Firefly III is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Firefly III. If not, see <http://www.gnu.org/licenses/>.
 */
declare(strict_types=1);

namespace FireflyIII\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class PiggyBank.
 *
 * @property Carbon  $targetdate
 * @property Carbon  $startdate
 * @property string  $targetamount
 * @property int     $id
 * @property string  $name
 * @property Account $account
 * @property Carbon  $updated_at
 * @property Carbon  $created_at
 * @property int     $order
 * @property bool    $active
 * @property int     $account_id
 * @property bool    encrypted
 *
 */
class PiggyBank extends Model
{
    use SoftDeletes;

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts
    = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'startdate'  => 'date',
        'targetdate' => 'date',
        'order'      => 'int',
        'active'     => 'boolean',
        'encrypted'  => 'boolean',
    ];
    /** @var array Fields that can be filled */
    protected $fillable = ['name', 'account_id', 'order', 'targetamount', 'startdate', 'targetdate', 'active'];
    /** @var array Hidden from view */
    protected $hidden = ['targetamount_encrypted', 'encrypted'];

    /**
     * Route binder. Converts the key in the URL to the specified object (or throw 404).
     *
     * @param string $value
     *
     * @return PiggyBank
     * @throws NotFoundHttpException
     */
    public static function routeBinder(string $value): PiggyBank
    {
        if (auth()->check()) {
            $piggyBankId = (int)$value;
            $piggyBank   = self::where('piggy_banks.id', $piggyBankId)
                ->leftJoin('accounts', 'accounts.id', '=', 'piggy_banks.account_id')
                ->where('accounts.user_id', auth()->user()->id)->first(['piggy_banks.*']);
            if (null !== $piggyBank) {
                return $piggyBank;
            }
        }
        throw new NotFoundHttpException;
    }

    /**
     * @codeCoverageIgnore
     * @return BelongsTo
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * @codeCoverageIgnore
     * Get all of the piggy bank's notes.
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'noteable');
    }

    /**
     * @codeCoverageIgnore
     * @return HasMany
     */
    public function piggyBankEvents(): HasMany
    {
        return $this->hasMany(PiggyBankEvent::class);
    }

    /**
     * display Piggy bank accounts with total values adding and subtracting transfers as needed
     * @codeCoverageIgnore
     * @return HasMany
     */
    public function accounts(): HasMany
    {
        $events = $this->hasMany(PiggyBankEvent::class)->selectRaw('
            piggy_bank_events.account_id as id,accounts.name, `account_meta`.`data`, SUM(piggy_bank_events.amount) as sum, (select SUM(p1.transfer) FROM `piggy_bank_events` as p1 where p1.account_id=piggy_bank_events.account_id and p1.piggy_bank_id=piggy_bank_events.piggy_bank_id) as transfers, (select SUM(p2.transfer) FROM `piggy_bank_events` as p2 where p2.from_account_id=piggy_bank_events.account_id and p2.piggy_bank_id=piggy_bank_events.piggy_bank_id) as withdrawals
        ')
            ->join('accounts', 'piggy_bank_events.account_id', '=', 'accounts.id')
            ->join('account_meta', 'accounts.id', '=', 'account_meta.account_id')
            ->where('account_meta.name', 'currency_id')
            ->groupBy('piggy_bank_events.account_id')
            ->groupBy('account_meta.data')
            ->groupBy('piggy_bank_events.piggy_bank_id');
        return $events;
    }

    /**
     * @codeCoverageIgnore
     * @return HasMany
     */
    public function piggyBankRepetitions(): HasMany
    {
        return $this->hasMany(PiggyBankRepetition::class);
    }

    /**
     * @codeCoverageIgnore
     *
     * @param $value
     */
    public function setTargetamountAttribute($value): void
    {
        $this->attributes['targetamount'] = (string)$value;
    }
}
