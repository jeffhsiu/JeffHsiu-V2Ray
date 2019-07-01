<?php

namespace App\Http\Controllers\Admin\VPS;

use App\Models\VPS\ServerLog;
use Backpack\CRUD\app\Http\Controllers\CrudController;

// VALIDATION: change the requests to match your own file names if you need form validation
use App\Http\Requests\VPS\ServerLogRequest as StoreRequest;
use App\Http\Requests\VPS\ServerLogRequest as UpdateRequest;
use Backpack\CRUD\CrudPanel;

/**
 * Class ServerLogCrudController
 * @package App\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class ServerLogCrudController extends CrudController
{
    public function setup()
    {
        /*
        |--------------------------------------------------------------------------
        | CrudPanel Basic Information
        |--------------------------------------------------------------------------
        */
        $this->crud->setModel('App\Models\VPS\ServerLog');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/vps/serverlog');
        $this->crud->setEntityNameStrings('serverlog', 'server_logs');

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
                'model' => "App\User", // foreign key model
            ],
            [
                // 1-n relationship
                'name' => 'order_id', // the column that contains the ID of that connected entity;
                'label' => 'Customer', // Table column heading
                'type' => 'select',
                'entity' => 'order', // the method that defines the relationship in your Model
                'attribute' => 'customer_name', // foreign key attribute that is shown to user
                'model' => "App\Models\Order\Order", // foreign key model
            ],
            [
                'name' => 'ip',
                'label' => 'IP',
                'type' => 'text',
            ],
            [
                'name' => 'docker_name',
                'label' => 'Docker Name',
                'type' => 'text',
            ],
            [
                'name' => 'action',
                'label' => 'Action',
                'type' => 'select_from_array',
                'options' => [
                    ServerLog::ACTION_DOCKER_START => 'Docker Start',
                    ServerLog::ACTION_DOCKER_STOP => 'Docker Stop',
                    ServerLog::ACTION_DOCKER_REDO => 'Docker Redo',
                    ServerLog::ACTION_DOCKER_RESTART => 'Docker Restart',
                    ServerLog::ACTION_SERVER_START => 'Server Start',
                    ServerLog::ACTION_SERVER_STOP => 'Server Stop',
                    ServerLog::ACTION_SERVER_RESTART => 'Server Restart',
                ],
            ],
            [
                'name' => 'reason',
                'label' => 'Reason',
                'type' => 'text',
            ],
            [
                'name' => "created_at", // The db column name
                'label' => "Create Date", // Table column heading
                'type' => "datetime-null",
                'format' => "YYYY-MM-DD hh:m:s", // use something else than the base.default_datetime_format config value
            ],
        ]);


        $this->crud->removeAllButtons();

        // add asterisk for fields that are required in ServerLogRequest
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
