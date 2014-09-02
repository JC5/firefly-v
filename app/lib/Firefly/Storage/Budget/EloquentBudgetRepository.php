<?php

namespace Firefly\Storage\Budget;

use Carbon\Carbon;

/**
 * Class EloquentBudgetRepository
 *
 * @package Firefly\Storage\Budget
 */
class EloquentBudgetRepository implements BudgetRepositoryInterface
{
    protected $_user = null;

    /**
     *
     */
    public function __construct()
    {
        $this->_user = \Auth::user();
    }

    /**
     * @param \Budget $budget
     *
     * @return bool|mixed
     */
    public function destroy(\Budget $budget)
    {
        $budget->delete();

        return true;
    }

    /**
     * @param $budgetId
     *
     * @return mixed
     */
    public function find($budgetId)
    {

        return $this->_user->budgets()->find($budgetId);
    }

    public function findByName($budgetName)
    {

        return $this->_user->budgets()->whereName($budgetName)->first();
    }

    /**
     * @return mixed
     */
    public function get()
    {
        $set = $this->_user->budgets()->with(
                    ['limits'                        => function ($q) {
                            $q->orderBy('limits.startdate', 'DESC');
                        }, 'limits.limitrepetitions' => function ($q) {
                            $q->orderBy('limit_repetitions.startdate', 'ASC');
                        }]
        )->orderBy('name', 'ASC')->get();
        foreach ($set as $budget) {
            foreach ($budget->limits as $limit) {
                foreach ($limit->limitrepetitions as $rep) {
                    $rep->left = $rep->left();
                }
            }
        }

        return $set;
    }

    /**
     * @return array|mixed
     */
    public function getAsSelectList()
    {
        $list   = $this->_user->budgets()->with(
                       ['limits', 'limits.limitrepetitions']
        )->orderBy('name', 'ASC')->get();
        $return = [];
        foreach ($list as $entry) {
            $return[intval($entry->id)] = $entry->name;
        }

        return $return;
    }


    /**
     * @param $data
     *
     * @return \Budget|mixed
     */
    public function store($data)
    {
        $budget       = new \Budget;
        $budget->name = $data['name'];
        $budget->user()->associate($this->_user);
        $budget->save();

        // if limit, create limit (repetition itself will be picked up elsewhere).
        if (isset($data['amount']) && floatval($data['amount']) > 0) {
            $limit = new \Limit;
            $limit->budget()->associate($budget);
            $startDate = new Carbon;
            switch ($data['repeat_freq']) {
                case 'daily':
                    $startDate->startOfDay();
                    break;
                case 'weekly':
                    $startDate->startOfWeek();
                    break;
                case 'monthly':
                    $startDate->startOfMonth();
                    break;
                case 'quarterly':
                    $startDate->firstOfQuarter();
                    break;
                case 'half-year':
                    $startDate->startOfYear();
                    if (intval($startDate->format('m')) >= 7) {
                        $startDate->addMonths(6);
                    }
                    break;
                case 'yearly':
                    $startDate->startOfYear();
                    break;
            }
            $limit->startdate   = $startDate;
            $limit->amount      = $data['amount'];
            $limit->repeats     = isset($data['repeats']) ? $data['repeats'] : 0;
            $limit->repeat_freq = $data['repeat_freq'];
            if ($limit->validate()) {
                $limit->save();
                \Event::fire('limits.store', [$limit]);
            }
        }
        if ($budget->validate()) {
            $budget->save();
        }

        return $budget;
    }

    /**
     * @param \Budget $budget
     * @param         $data
     *
     * @return \Budget|mixed
     */
    public function update(\Budget $budget, $data)
    {
        // update account accordingly:
        $budget->name = $data['name'];
        if ($budget->validate()) {
            $budget->save();
        }

        return $budget;
    }

    /**
     * @param \User $user
     * @return mixed|void
     */
    public function overruleUser(\User $user)
    {
        $this->_user = $user;
        return true;
    }

} 