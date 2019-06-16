<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Models\Finance\Cost;
use Backpack\CRUD\app\Http\Controllers\CrudController;

// VALIDATION: change the requests to match your own file names if you need form validation
use App\Http\Requests\Finance\CostRequest as StoreRequest;
use App\Http\Requests\Finance\CostRequest as UpdateRequest;
use Backpack\CRUD\CrudPanel;
use Illuminate\Support\Facades\Storage;

/**
 * Class CostCrudController
 * @package App\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class CostCrudController extends CrudController
{
    public function setup()
    {
        /*
        |--------------------------------------------------------------------------
        | CrudPanel Basic Information
        |--------------------------------------------------------------------------
        */
        $this->crud->setModel('App\Models\Finance\Cost');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/finance/cost');
        $this->crud->setEntityNameStrings('cost', 'costs');

        /*
        |--------------------------------------------------------------------------
        | CrudPanel Configuration
        |--------------------------------------------------------------------------
        */
        $this->crud->addColumns([
            [
                'name' => 'title', // The db column name
                'label' => 'Title', // Table column heading
                'type' => 'text',
            ],
            [
                'name' => 'descript', // The db column name
                'label' => 'Descript', // Table column heading
                'type' => 'text',
            ],
            [
                'name' => 'status',
                'label' => 'Status',
                'type' => 'select_from_array',
                'options' => [
                    Cost::STATUS_UNSETTLED => 'Unsettled',
                    Cost::STATUS_SETTLED => 'Settled',
                ],
            ],
            [
                'name' => 'image', // The db column name
                'label' => 'Image', // Table column heading
                'type' => 'image',
                'disk' => 'public',
                 'height' => '500px',
                // 'width' => '30px',
                'visibleInTable' => false, // no point, since it's a large text
            ],
            [
                'name' => 'amount', // The db column name
                'label' => 'Amount', // Table column heading
                'type' => 'number',
                'prefix' => '¥ ',
            ],
            [
                'name' => "date", // The db column name
                'label' => "Date", // Table column heading
                'type' => "datetime-null",
                'format' => 'YYYY-MM-DD', // use something else than the base.default_datetime_format config value
            ],
        ]);

        $this->crud->addFields([
            [
                'name' => 'title',
                'label' => 'Title',
                'type' => 'text',
            ],
            [
                'name' => 'descript',
                'label' => 'Descript',
                'type' => 'text',
            ],
            [ // image
                'name' => 'image',
                'label' => 'Image',
                'type' => 'image',
                'upload' => true,
                'crop' => true, // set to true to allow cropping, false to disable
            ],
            [
                'name' => 'status',
                'label' => 'Status',
                'type' => 'select2_from_array',
                'options' => [
                    Cost::STATUS_UNSETTLED => 'Unsettled',
                    Cost::STATUS_SETTLED => 'Settled',
                ],
            ],
            [
                'name' => 'amount', // The db column name
                'label' => 'Amount', // Table column heading
                'type' => 'number',
                'prefix' => '¥ ',
            ],
            [   // DateTime
                'name' => 'date',
                'label' => 'Date',
                'type' => 'datetime_picker',
                // optional:
                'datetime_picker_options' => [
                    'format' => 'YYYY-MM-DD',
                ],
                'allows_null' => true,
                'default' => date('Y-m-d'),
            ],
        ]);

        $this->crud->allowAccess('show');

        // add asterisk for fields that are required in CostRequest
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
