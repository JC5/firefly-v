<?php

/*
 * DateController.php
 * Copyright (c) 2021 james@firefly-iii.org
 *
 * This file is part of Firefly III (https://github.com/firefly-iii).
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace FireflyIII\Api\V1\Controllers\Insight\Expense;

use Carbon\Carbon;
use FireflyIII\Api\V1\Controllers\Controller;
use FireflyIII\Api\V1\Requests\DateRequest;
use FireflyIII\Models\AccountType;
use FireflyIII\Repositories\Account\AccountRepositoryInterface;
use FireflyIII\Repositories\Currency\CurrencyRepositoryInterface;
use FireflyIII\Support\Http\Api\ApiSupport;
use FireflyIII\User;
use Illuminate\Http\JsonResponse;

/**
 * TODO per object group?
 * TODO transfers voor piggies?
 * TODO currency?
 * TODO net worth?
 *
 * Class AccountController
 *
 * Shows expense information grouped or limited by date.
 * Ie. all expenses grouped by account + currency.
 *
 * /api/v1/insight/expenses/expense
 *  Expenses grouped by expense account. Can be limited by date and by asset account.
 * /api/v1/insight/expenses/asset
 *  Expenses grouped by asset account. Can be limited by date and by asset account.
 * /api/v1/insight/expenses/total
 *  Expenses, total (no filter). Can be limited by date and by asset account.
 * /api/v1/insight/expenses/budget
 *  Expenses per budget or no budget. Can be limited by date and by asset account.
 * /api/v1/insight/expenses/budget
 *  Also per budget limit.
 * /api/v1/insight/expenses/category
 *  Expenses per category or no category. Can be limited by date and by asset account.
 * /api/v1/insight/expenses/bill
 *  Expenses per bill or no bill. Can be limited by date and by asset account.
 *
 */
class AccountController extends Controller
{
    use ApiSupport;

    private CurrencyRepositoryInterface $currencyRepository;
    private AccountRepositoryInterface  $repository;

    /**
     * AccountController constructor.
     *
     * @codeCoverageIgnore
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware(
            function ($request, $next) {
                /** @var User $user */
                $user             = auth()->user();
                $this->repository = app(AccountRepositoryInterface::class);
                $this->repository->setUser($user);

                $this->currencyRepository = app(CurrencyRepositoryInterface::class);
                $this->currencyRepository->setUser($user);

                return $next($request);
            }
        );
    }

    /**
     * @param DateRequest $request
     *
     * @return JsonResponse
     */
    public function expense(DateRequest $request): JsonResponse
    {
        $dates = $request->getAll();
        /** @var Carbon $start */
        $start = $dates['start'];
        /** @var Carbon $end */
        $end = $dates['end'];

        $start->subDay();

        // prep some vars:
        $currencies = [];
        $tempData   = [];

        // grab all accounts and names
        $accounts      = $this->repository->getAccountsByType([AccountType::EXPENSE]);
        $accountNames  = $this->extractNames($accounts);
        $startBalances = app('steam')->balancesPerCurrencyByAccounts($accounts, $start);
        $endBalances   = app('steam')->balancesPerCurrencyByAccounts($accounts, $end);

        // loop the end balances. This is an array for each account ($expenses)
        foreach ($endBalances as $accountId => $expenses) {
            $accountId = (int)$accountId;
            // loop each expense entry (each entry can be a different currency).
            foreach ($expenses as $currencyId => $endAmount) {
                $currencyId = (int)$currencyId;

                // see if there is an accompanying start amount.
                // grab the difference and find the currency.
                $startAmount             = $startBalances[$accountId][$currencyId] ?? '0';
                $diff                    = bcsub($endAmount, $startAmount);
                $currencies[$currencyId] = $currencies[$currencyId] ?? $this->currencyRepository->findNull($currencyId);
                if (0 !== bccomp($diff, '0')) {
                    // store the values in a temporary array.
                    $tempData[] = [
                        'id'               => $accountId,
                        'name'             => $accountNames[$accountId],
                        'difference'       => bcmul($diff, '-1'),
                        'difference_float' => ((float)$diff) * -1,
                        'currency_id'      => (string) $currencyId,
                        'currency_code'    => $currencies[$currencyId]->code,
                    ];
                }
            }
        }


        // sort temp array by amount.
        $amounts = array_column($tempData, 'difference_float');
        array_multisort($amounts, SORT_ASC, $tempData);

        return response()->json($tempData);
    }

}