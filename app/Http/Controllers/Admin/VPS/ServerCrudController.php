<?php

namespace App\Http\Controllers\Admin\VPS;

use App\Models\VPS\Server;
use Backpack\CRUD\app\Http\Controllers\CrudController;

// VALIDATION: change the requests to match your own file names if you need form validation
use App\Http\Requests\VPS\ServerRequest as StoreRequest;
use App\Http\Requests\VPS\ServerRequest as UpdateRequest;
use Backpack\CRUD\CrudPanel;

/**
 * Class ServerCrudController
 * @package App\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class ServerCrudController extends CrudController
{
    public function setup()
    {
        /*
        |--------------------------------------------------------------------------
        | CrudPanel Basic Information
        |--------------------------------------------------------------------------
        */
        $this->crud->setModel('App\Models\VPS\Server');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/vps/server');
        $this->crud->setEntityNameStrings('server', 'servers');

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
                'name' => 'ip', // The db column name
                'label' => 'IP', // Table column heading
                'type' => 'text',
            ],
            [
                'name' => 'account',
                'label' => 'Account',
                'type' => 'text',
            ],
            [
                'name' => "end_date", // The db column name
                'label' => "Rend Due Date", // Table column heading
                'type' => "datetime",
                'format' => 'YYYY-MM-DD', // use something else than the base.default_datetime_format config value
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
                'name' => 'ip', // The db column name
                'label' => 'IP', // Table column heading
                'type' => 'text',
            ],
            [
                'name' => 'account',
                'label' => 'Account',
                'type' => 'text',
            ],
            [   // Number
                'name' => 'ssh_port',
                'label' => 'SSH Port',
                'type' => 'number',
                // optionals
                // 'attributes' => ["step" => "any"], // allow decimals
                // 'prefix' => "$",
                // 'suffix' => ".00",
                'default' => 22,
            ],
            [
                'name' => 'ssh_pwd',
                'label' => 'SSH Password',
                'type' => 'password',
            ],
            [   // DateTime
                'name' => 'end_date',
                'label' => 'Rend Due Date',
                'type' => 'datetime_picker',
                // optional:
                'datetime_picker_options' => [
                    'format' => 'YYYY-MM-DD',
                ],
                'allows_null' => true,
                // 'default' => '2017-05-12 11:59:59',
            ],
        ]);

        /*
         * Filter
         */
        $this->crud->addFilter([
            'name' => 'provider',
            'label'=> 'Provider',
            'type' => 'dropdown',
        ], [
            Server::PROVIDER_GOOGLE => 'Google Cloud',
            Server::PROVIDER_BANDWAGON => 'Bandwagon',
        ], function($value) { // if the filter is active
            $this->crud->addClause('where', 'provider', $value);
        });
        $this->crud->addFilter([
            'name' => 'ip',
            'label'=> 'IP',
            'type' => 'text',
        ], false,
            function($value) { // if the filter is active
                $this->crud->addClause('where', 'ip', 'LIKE', "%$value%");
        });
        $this->crud->addFilter([
            'name' => 'account',
            'label'=> 'Account',
            'type' => 'text',
        ], false,
            function($value) { // if the filter is active
                $this->crud->addClause('where', 'account', 'LIKE', "%$value%");
        });
        $this->crud->addFilter([ // daterange filter
            'name' => 'end_date',
            'label'=> 'Rend Due Date',
            'type' => 'date_range',
        ], false,
            function($value) {
                $dates = json_decode($value);
                $this->crud->addClause('where', 'end_date', '>=', $dates->from);
                $this->crud->addClause('where', 'end_date', '<=', $dates->to . ' 23:59:59');
        });

        $this->crud->allowAccess('show');

        // add asterisk for fields that are required in ServerRequest
        $this->crud->setRequiredFields(StoreRequest::class, 'create');
        $this->crud->setRequiredFields(UpdateRequest::class, 'edit');
    }

    public function show($id)
    {
        $content = parent::show($id);

        $this->crud->removeColumn('ssh_pwd');

        $this->crud->addColumns([
            [
                'name' => 'ssh_port',
                'label' => "SSH Port",
                'type' => 'number',
            ],

        ]);
        if (backpack_user()->can('vps-server-sshpwd')) {
            $this->crud->addColumn(
                [
                    'name' => 'ssh_pwd',
                    'label' => 'SSH Password',
                    'type' => 'text',
                ]
            );
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
