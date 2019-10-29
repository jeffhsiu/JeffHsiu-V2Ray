<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Models\Finance\Cost;
use App\Models\Finance\Settlement;
use App\Models\Order\Order;
use Backpack\CRUD\app\Http\Controllers\CrudController;

// VALIDATION: change the requests to match your own file names if you need form validation
use App\Http\Requests\Finance\SettlementRequest as StoreRequest;
use App\Http\Requests\Finance\SettlementRequest as UpdateRequest;
use Backpack\CRUD\CrudPanel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Prologue\Alerts\Facades\Alert;

/**
 * Class SettlementCrudController
 * @package App\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class SettlementCrudController extends CrudController
{
    public function setup()
    {
        /*
        |--------------------------------------------------------------------------
        | CrudPanel Basic Information
        |--------------------------------------------------------------------------
        */
        $this->crud->setModel('App\Models\Finance\Settlement');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/finance/settlement');
        $this->crud->setEntityNameStrings('settlement', 'settlements');

        /*
        |--------------------------------------------------------------------------
        | CrudPanel Configuration
        |--------------------------------------------------------------------------
        */
        $this->crud->addColumns([
            [
                // 1-n relationship
                'name' => 'user_id', // the column that contains the ID of that connected entity;
                'label' => 'User', // Table column heading
                'type' => 'select',
                'entity' => 'user', // the method that defines the relationship in your Model
                'attribute' => 'name', // foreign key attribute that is shown to user
                'model' => 'App\User', // foreign key model
            ],
            [
                'name' => 'revenue', // The db column name
                'label' => 'Revenue', // Table column heading
                'type' => 'number',
                'prefix' => '¥ ',
            ],
            [
                'name' => 'cost', // The db column name
                'label' => 'Cost', // Table column heading
                'type' => 'number',
                'prefix' => '¥ ',
            ],
            [
                'name' => 'net_profit', // The db column name
                'label' => 'Net Profit', // Table column heading
                'type' => 'number',
                'prefix' => '¥ ',
            ],
            [
                'name' => 'remark', // The db column name
                'label' => 'Remark', // Table column heading
                'type' => 'text',
            ],
            [
                'name' => "created_at", // The db column name
                'label' => "Settle Date", // Table column heading
                'type' => "datetime-null",
                'format' => 'YYYY-MM-DD', // use something else than the base.default_datetime_format config value
            ],
        ]);

        $this->crud->removeAllButtons();

        $this->crud->addButtonFromView('top', 'settle', 'settle', 'end');

        // add asterisk for fields that are required in SettlementRequest
        $this->crud->setRequiredFields(StoreRequest::class, 'create');
        $this->crud->setRequiredFields(UpdateRequest::class, 'edit');
    }

    public function store(StoreRequest $request)
    {
        // your additional operations before save here
        $redirect_location = parent::storeCrud($request);
        // your additional operations after save here
        // use $this->data['entry'] or $this->crud->entry
        return $redirect_location;
    }

    public function update(UpdateRequest $request)
    {
        // your additional operations before save here
        $redirect_location = parent::updateCrud($request);
        // your additional operations after save here
        // use $this->data['entry'] or $this->crud->entry
        return $redirect_location;
    }

    public function settlePreview(Request $request)
    {
        $data = array();

        $data['orders'] = Order::where('settlement_id', 0)
            ->where('type', Order::TYPE_PAID)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        $data['total_revenue'] = Order::where('settlement_id', 0)
            ->where('type', Order::TYPE_PAID)
            ->sum('profit');
        $data['costs'] = Cost::where('status', Cost::STATUS_UNSETTLED)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        $data['admin_costs'] = Cost::selectRaw('user_id, SUM(amount) as amount')
            ->where('status', Cost::STATUS_UNSETTLED)
            ->groupBy('user_id')
            ->get();
        $data['total_cost'] = Cost::where('status', Cost::STATUS_UNSETTLED)
            ->sum('amount');
        $data['net_profit'] = $data['total_revenue'] - $data['total_cost'];

        $last_settle = Settlement::orderBy('created_at', 'desc')->first();
        $data['start_date'] = $last_settle ? explode(' ', $last_settle->created_at)[0] : '-';
        $data['end_date'] = date('Y-m-d');

        return view('admin.finance.settlement.preview', $data);
    }

    public function settleIncomes(Request $request)
    {
        DB::transaction(function() use ($request) {
            $orders = Order::where('settlement_id', 0)
                ->where('type', Order::TYPE_PAID);
            $total_revenue = Order::where('settlement_id', 0)
                ->where('type', Order::TYPE_PAID)
                ->sum('profit');
            $costs = Cost::where('status', Cost::STATUS_UNSETTLED)
                ->orderBy('created_at', 'desc');
            $total_cost = Cost::where('status', Cost::STATUS_UNSETTLED)
                ->sum('amount');
            $net_profit = $total_revenue - $total_cost;

            $settlement= Settlement::create([
                'user_id' => auth()->id(),
                'revenue' => $total_revenue,
                'cost' => $total_cost,
                'net_profit' => $net_profit,
                'remark' => $request->remark ?: ''
            ]);
            $orders->update(['settlement_id' => $settlement->id]);
            $costs->update(['settlement_id' => $settlement->id, 'status' => Cost::STATUS_SETTLED]);
        });

        Alert::success("Settle incomes success.")->flash();
        return redirect(backpack_url('finance/settlement'));
    }
}
