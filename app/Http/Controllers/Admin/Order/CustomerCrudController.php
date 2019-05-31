<?php

namespace App\Http\Controllers\Admin\Order;

use Backpack\CRUD\app\Http\Controllers\CrudController;

// VALIDATION: change the requests to match your own file names if you need form validation
use App\Http\Requests\Order\CustomerRequest as StoreRequest;
use App\Http\Requests\Order\CustomerRequest as UpdateRequest;
use Backpack\CRUD\CrudPanel;

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

        $this->crud->orderBy('id', 'desc');

        $this->crud->allowAccess('show');

        // add asterisk for fields that are required in CustomerRequest
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
}
