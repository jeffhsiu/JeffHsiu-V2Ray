<?php

namespace App\Http\Controllers\Admin\Order;

use App\Models\Order\Distributor;
use Backpack\CRUD\app\Http\Controllers\CrudController;

// VALIDATION: change the requests to match your own file names if you need form validation
use App\Http\Requests\Order\CustomerCreateRequest as StoreRequest;
use App\Http\Requests\Order\CustomerRequest as UpdateRequest;
use Backpack\CRUD\CrudPanel;
use Illuminate\Support\Facades\Request;

/**
 * Class CustomerCrudController
 * @package App\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class CustomerCrudController extends CrudController
{
    public function setup()
    {
        /*
        |--------------------------------------------------------------------------
        | CrudPanel Basic Information
        |--------------------------------------------------------------------------
        */
        $this->crud->setModel('App\Models\Order\Customer');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/order/customer');
        $this->crud->setEntityNameStrings('customer', 'customers');

        /*
        |--------------------------------------------------------------------------
        | CrudPanel Configuration
        |--------------------------------------------------------------------------
        */
        $this->crud->addColumns([
            [
                'name' => 'name',
                'label' => 'Name',
                'type' => 'text',
            ],
            [
                'name' => 'distributor_id', // The db column name
                'label' => 'Distributor', // Table column heading
                'type' => 'model_function',
                'function_name' => 'getDistributorLink',
                'limit' => 1000
            ],
            [
                'name' => 'wechat_id',
                'label' => 'Wechat ID',
                'type' => 'text',
            ],
            [
                'name' => 'facebook_id',
                'label' => 'Facebook ID',
                'type' => 'text',
                'limit' => 100
            ],
            [
                'name' => 'email',
                'label' => 'Email',
                'type' => 'text',
            ],
            [
                'name' => 'mobile',
                'label' => 'Mobile',
                'type' => 'text',
            ],
        ]);

        $this->crud->addFields([
            [
                'name' => 'name',
                'label' => 'Name',
                'type' => 'text',
            ],
            [
                // 1-n relationship
                'name' => 'distributor_id', // the column that contains the ID of that connected entity;
                'label' => 'Distributor', // Table column heading
                'type' => 'select2-notnull',
                'entity' => 'distributor', // the method that defines the relationship in your Model
                'attribute' => 'name', // foreign key attribute that is shown to user
                'model' => 'App\Models\Order\Distributor', // foreign key model
                'options'   => (function ($query) {
                    if (auth()->user()->hasRole('Distributor')) {
                        return $query->where('id', auth()->user()->distributor->id)->get();
                    } else {
                        return $query->get();
                    }
                }),
            ],
            [
                'name' => 'wechat_id',
                'label' => 'Wechat ID',
                'type' => 'text',
            ],
            [
                'name' => 'facebook_id',
                'label' => 'Facebook ID',
                'type' => 'text',
            ],
            [
                'name' => 'email',
                'label' => 'Email',
                'type' => 'text',
            ],
            [
                'name' => 'mobile',
                'label' => 'Mobile',
                'type' => 'text',
            ],
            [
                'name' => 'remark',
                'label' => 'Remark',
                'type' => 'text',
            ],
        ]);

        // 經銷商只能看到他自己的
        if (auth()->user()->hasRole('Distributor')) {
            $this->crud->addClause('where', 'distributor_id', auth()->user()->distributor->id);
        }

        $this->crud->orderBy('id', 'desc');

        $this->crud->allowAccess('show');

        // add asterisk for fields that are required in CustomerRequest
        $this->crud->setRequiredFields(StoreRequest::class, 'create');
        $this->crud->setRequiredFields(UpdateRequest::class, 'edit');
    }

    /**
     * Show the form for creating inserting a new row.
     *
     * @return Response
     */
    public function create()
    {
        parent::create();
        $this->data['from_server'] = Request::has('server_id') ? true : false;
        $this->data['server_id'] = Request::has('server_id') ? Request::get('server_id') : 1;
        $this->data['docker_name'] = Request::has('docker_name') ? Request::get('docker_name') : 'v2ray-01';

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view('admin.order.customer.create', $this->data);
    }

    public function store(StoreRequest $request)
    {
        $server_id = $request->server_id;
        $docker_name = $request->docker_name;
        $request->offsetUnset('server_id');
        $request->offsetUnset('docker_name');

        // your additional operations before save here
        $redirect_location = parent::storeCrud($request);
        // your additional operations after save here
        // use $this->data['entry'] or $this->crud->entry
        if ($request->save_action == 'save_to_order') {
            $redirectUrl = 'admin/order/order/create?server_id='.$server_id.'&docker_name='.$docker_name;
            $redirect_location = \Redirect::to($redirectUrl);
        }
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
}
