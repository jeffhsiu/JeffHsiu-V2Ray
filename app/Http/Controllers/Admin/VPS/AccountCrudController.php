<?php

namespace App\Http\Controllers\Admin\VPS;

use App\Models\VPS\Server;
use Backpack\CRUD\app\Http\Controllers\CrudController;

// VALIDATION: change the requests to match your own file names if you need form validation
use App\Http\Requests\VPS\AccountRequest as StoreRequest;
use App\Http\Requests\VPS\AccountRequest as UpdateRequest;
use Backpack\CRUD\CrudPanel;

/**
 * Class AccountCrudController
 * @package App\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class AccountCrudController extends CrudController
{
    public function setup()
    {
        /*
        |--------------------------------------------------------------------------
        | CrudPanel Basic Information
        |--------------------------------------------------------------------------
        */
        $this->crud->setModel('App\Models\VPS\Account');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/vps/account');
        $this->crud->setEntityNameStrings('account', 'accounts');

        /*
        |--------------------------------------------------------------------------
        | CrudPanel Configuration
        |--------------------------------------------------------------------------
        */
        $this->crud->addColumns([
            [
                'name' => 'provider',
                'label' => 'Provider',
                'type' => 'select_from_array',
                'options' => [
                    Server::PROVIDER_GOOGLE => 'Google Cloud',
                    Server::PROVIDER_BANDWAGON => 'Bandwagon',
                ],
            ],
            [
                'name' => 'account',
                'label' => 'Account',
                'type' => 'text',
                'priority' => 1,
            ],
        ]);

        $this->crud->addFields([
            [
                'name' => 'provider',
                'label' => 'Provider',
                'type' => 'select2_from_array',
                'options' => [
                    Server::PROVIDER_GOOGLE => 'Google Cloud',
                    Server::PROVIDER_BANDWAGON => 'Bandwagon',
                ],
            ],
            [
                'name' => 'account',
                'label' => 'Account',
                'type' => 'text',
            ],
            [
                'name' => 'password',
                'label' => 'Password',
                'type' => 'password',
            ],
        ]);

        $this->crud->allowAccess('show');

        // add asterisk for fields that are required in AccountRequest
        $this->crud->setRequiredFields(StoreRequest::class, 'create');
        $this->crud->setRequiredFields(UpdateRequest::class, 'edit');
    }

    public function show($id)
    {
        $content = parent::show($id);

        if (backpack_user()->can('vps-account-password')) {
            $this->crud->addColumn(
                [
                    'name' => 'password',
                    'label' => 'Password',
                    'type' => 'text',
                ]
            );
        } else {
            $this->crud->removeColumn('password');
        }

        return $content;
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
