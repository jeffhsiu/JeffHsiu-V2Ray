<?php

namespace App\Http\Controllers\Admin\Order;

use App\Models\Order\Customer;
use App\Models\Order\Distributor;
use App\Models\Order\Order;
use App\Models\VPS\Server;
use Backpack\CRUD\app\Http\Controllers\CrudController;

// VALIDATION: change the requests to match your own file names if you need form validation
use App\Http\Requests\Order\OrderRequest as StoreRequest;
use App\Http\Requests\Order\OrderRequest as UpdateRequest;
use Backpack\CRUD\CrudPanel;
use Illuminate\Support\Facades\Request;
use Prologue\Alerts\Facades\Alert;

/**
 * Class OrderCrudController
 * @package App\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class OrderCrudController extends CrudController
{
    public function setup()
    {
        /*
        |--------------------------------------------------------------------------
        | CrudPanel Basic Information
        |--------------------------------------------------------------------------
        */
        $this->crud->setModel('App\Models\Order\Order');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/order/order');
        $this->crud->setEntityNameStrings('order', 'orders');

        /*
        |--------------------------------------------------------------------------
        | CrudPanel Configuration
        |--------------------------------------------------------------------------
        */
        $this->crud->addColumns([
            [
                'name' => 'customer_id', // The db column name
                'label' => 'Customer', // Table column heading
                'type' => 'model_function',
                'function_name' => 'getCustomerLink',
                'limit' => 1000
            ],
            [
                'name' => 'distributor_id', // The db column name
                'label' => 'Distributor', // Table column heading
                'type' => 'model_function',
                'function_name' => 'getDistributorLink',
                'limit' => 1000
            ],
            [
                'name' => 'status',
                'label' => 'Status',
                'type' => 'select_from_array',
                'options' => [
                    Order::STATUS_ENABLE => 'Enable',
                    Order::STATUS_DISABLE => 'Disable',
                ],
            ],
            [
                'name' => 'type',
                'label' => 'Type',
                'type' => 'select_from_array',
                'options' => [
                    Order::TYPE_TRIAL => 'Trial',
                    Order::TYPE_PAID => 'Paid',
                ],
            ],
            [
                'name' => "start_date", // The db column name
                'label' => "Start Date", // Table column heading
                'type' => "datetime-null",
                'format' => 'YYYY-MM-DD', // use something else than the base.default_datetime_format config value
            ],
            [
                'name' => "end_date", // The db column name
                'label' => "End Date", // Table column heading
                'type' => "datetime-null",
                'format' => 'YYYY-MM-DD', // use something else than the base.default_datetime_format config value
            ],
            [
                'name' => 'server_id', // The db column name
                'label' => "Server IP", // Table column heading
                'type' => 'model_function',
                'function_name' => 'getServerIpLink',
                'limit' => 1000
            ],
            [
                'name' => 'docker_name', // The db column name
                'label' => 'Docker Name', // Table column heading
                'type' => 'text',
            ],
            [
                'name' => 'price', // The db column name
                'label' => 'Price', // Table column heading
                'type' => 'number',
                'prefix' => '¥ ',
            ],
            [
                'name' => 'commission', // The db column name
                'label' => 'Commission', // Table column heading
                'type' => 'number',
                'prefix' => '¥ ',
            ],
            [
                'name' => 'profit', // The db column name
                'label' => 'Profit', // Table column heading
                'type' => 'number',
                'prefix' => '¥ ',
            ],
            [
                'name' => 'remark', // The db column name
                'label' => 'Remark', // Table column heading
                'type' => 'text',
            ],
        ]);

        $this->crud->addFields([
            [  // Select2
                'name' => 'customer_id', // the db column for the foreign key
                'label' => "Customer",
                'type' => 'select2-notnull',
                'entity' => 'customer', // the method that defines the relationship in your Model
                'attribute' => 'name', // foreign key attribute that is shown to user
                'model' => 'App\Models\Order\Customer', // foreign key model
                'options'   => (function ($query) {
                    return $query->orderBy('id', 'desc')->get();
                })
            ],
            [
                // 1-n relationship
                'name' => 'distributor_id', // the column that contains the ID of that connected entity;
                'label' => 'Distributor', // Table column heading
                'type' => 'select2-notnull',
                'entity' => 'distributor', // the method that defines the relationship in your Model
                'attribute' => 'name', // foreign key attribute that is shown to user
                'model' => 'App\Models\Order\Distributor', // foreign key model
            ],
            [
                'name' => 'status',
                'label' => 'Status',
                'type' => 'select2_from_array',
                'options' => [
                    Order::STATUS_ENABLE => 'Enable',
                    Order::STATUS_DISABLE => 'Disable',
                ],
            ],
            [
                'name' => 'type',
                'label' => 'Type',
                'type' => 'select2_from_array',
                'options' => [
                    Order::TYPE_TRIAL => 'Trial',
                    Order::TYPE_PAID => 'Paid',
                ],
            ],
            [   // DateTime
                'name' => 'start_date',
                'label' => 'Start Date',
                'type' => 'datetime_picker',
                // optional:
                'datetime_picker_options' => [
                    'format' => 'YYYY-MM-DD',
                ],
                'allows_null' => true,
                'default' => date('Y-m-d'),
            ],
            [   // DateTime
                'name' => 'end_date',
                'label' => 'End Date',
                'type' => 'datetime_picker',
                // optional:
                'datetime_picker_options' => [
                    'format' => 'YYYY-MM-DD',
                ],
                'allows_null' => true,
                // 'default' => '2017-05-12 11:59:59',
            ],
            [
                // 1-n relationship
                'name' => 'server_id', // the column that contains the ID of that connected entity;
                'label' => 'Server', // Table column heading
                'type' => 'select2-notnull',
                'entity' => 'server', // the method that defines the relationship in your Model
                'attribute' => 'ip', // foreign key attribute that is shown to user
                'model' => 'App\Models\VPS\Server', // foreign key model
                'default' => Request::has('server_id') ? Request::get('server_id') : 1
            ],
            [
                'name' => 'docker_name', // The db column name
                'label' => 'Docker Name', // Table column heading
                'type' => 'select2_from_array',
                'options' => [
                    'v2ray-01' => 'v2ray-01',
                    'v2ray-02' => 'v2ray-02',
                    'v2ray-03' => 'v2ray-03',
                    'v2ray-04' => 'v2ray-04',
                    'v2ray-05' => 'v2ray-05',
                    'v2ray-06' => 'v2ray-06',
                    'v2ray-07' => 'v2ray-07',
                    'v2ray-08' => 'v2ray-08',
                    'v2ray-09' => 'v2ray-09',
                    'v2ray-10' => 'v2ray-10',
                ],
                'default' => Request::has('docker_name') ? Request::get('docker_name') : 'v2ray-01'
            ],
            [
                'name' => 'price', // The db column name
                'label' => 'Price', // Table column heading
                'type' => 'number',
                'prefix' => '¥ ',
            ],
            [
                'name' => 'commission', // The db column name
                'label' => 'Commission', // Table column heading
                'type' => 'number',
                'prefix' => '¥ ',
            ],
            [
                'name' => 'profit', // The db column name
                'label' => 'Profit', // Table column heading
                'type' => 'number',
                'prefix' => '¥ ',
            ],
            [
                'name' => 'remark', // The db column name
                'label' => 'Remark', // Table column heading
                'type' => 'text',
            ],
        ]);

        /*
         * Filter
         */
        $this->crud->addFilter([
            'name' => 'customer_id',
            'label'=> 'Customer',
            'type' => 'text',
        ], false,
            function($value) { // if the filter is active
                $customer = Customer::where('name', 'LIKE', "%$value%")->get('id')->toArray();
                $this->crud->addClause('whereIn', 'customer_id', array_column($customer, 'id'));
            });
        $this->crud->addFilter([
            'name' => 'distributor_id',
            'label'=> 'Distributor',
            'type' => 'text',
        ], false,
            function($value) { // if the filter is active
                $distributor = Distributor::where('name', 'LIKE', "%$value%")->get('id')->toArray();
                $this->crud->addClause('whereIn', 'distributor_id', array_column($distributor, 'id'));
            });
        $this->crud->addFilter([
            'name' => 'status',
            'label'=> 'Status',
            'type' => 'dropdown',
        ], [
            Order::STATUS_ENABLE => 'Enable',
            Order::STATUS_DISABLE => 'Disable',
        ],
            function($value) { // if the filter is active
                $this->crud->addClause('where', 'status', $value);
            });
        $this->crud->addFilter([
            'name' => 'type',
            'label'=> 'Type',
            'type' => 'dropdown',
        ], [
            Order::TYPE_TRIAL => 'Trial',
            Order::TYPE_PAID => 'Paid',
        ],
            function($value) { // if the filter is active
                $this->crud->addClause('where', 'type', $value);
            });
        $this->crud->addFilter([ // daterange filter
            'name' => 'start_date',
            'label'=> 'Start Date',
            'type' => 'date_range',
        ], false,
            function($value) {
                $dates = json_decode($value);
                $this->crud->addClause('where', 'start_date', '>=', $dates->from);
                $this->crud->addClause('where', 'start_date', '<=', $dates->to . ' 23:59:59');
            });
        $this->crud->addFilter([ // daterange filter
            'name' => 'end_date',
            'label'=> 'End Date',
            'type' => 'date_range',
        ], false,
            function($value) {
                $dates = json_decode($value);
                $this->crud->addClause('where', 'end_date', '>=', $dates->from);
                $this->crud->addClause('where', 'end_date', '<=', $dates->to . ' 23:59:59');
            });
        $this->crud->addFilter([
            'name' => 'server_id',
            'label'=> 'Server IP',
            'type' => 'text',
        ], false,
            function($value) { // if the filter is active
                $server = Server::where('ip', 'LIKE', "%$value%")->get('id')->toArray();
                $this->crud->addClause('whereIn', 'server_id', array_column($server, 'id'));
            });
        $this->crud->addFilter([
            'name' => 'price',
            'label'=> 'Price',
            'type' => 'range',
            'label_from' => 'min',
            'label_to' => 'max'
        ], false,
            function($value) { // if the filter is active
                $range = json_decode($value);
                if ($range->from) {
                    $this->crud->addClause('where', 'price', '>=', (float) $range->from);
                }
                if ($range->to) {
                    $this->crud->addClause('where', 'price', '<=', (float) $range->to);
                }
            });

        $this->crud->orderBy('id', 'desc');

        $this->crud->allowAccess('show');
        $this->crud->enableExportButtons();

        // add asterisk for fields that are required in OrderRequest
        $this->crud->setRequiredFields(StoreRequest::class, 'create');
        $this->crud->setRequiredFields(UpdateRequest::class, 'edit');
    }

    public function show($id)
    {
        $content = parent::show($id);

        $this->crud->addButtonFromView('line', 'config', 'config', 'end');
        $this->crud->addButtonFromView('line', 'qrcode', 'qrcode', 'end');

        return $content;
    }

    public function store(StoreRequest $request)
    {
        $order = Order::where('server_id', $request->server_id)
            ->where('docker_name', $request->docker_name)
            ->where('status', Order::STATUS_ENABLE)
            ->where('customer_id', '<>', $request->customer_id);
        if ($order->exists()) {
            Alert::error("The V2Ray account has been used.<br/> Please choose another setting or disable the account in use.")->flash();
            return redirect()->back();
        }
        // your additional operations before save here
        $redirect_location = parent::storeCrud($request);
        // your additional operations after save here
        // use $this->data['entry'] or $this->crud->entry
        $redirectUrl = $this->crud->route.'/'.$this->crud->entry->id;
        return redirect($redirectUrl);
    }

    public function update(UpdateRequest $request)
    {
        // your additional operations before save here
        $redirect_location = parent::updateCrud($request);
        // your additional operations after save here
        // use $this->data['entry'] or $this->crud->entry
        return $redirect_location;
    }
}
