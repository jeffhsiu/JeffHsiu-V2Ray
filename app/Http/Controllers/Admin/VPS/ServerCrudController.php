<?php

namespace App\Http\Controllers\Admin\VPS;

use App\Models\VPS\Server;
use Backpack\CRUD\app\Http\Controllers\CrudController;

// VALIDATION: change the requests to match your own file names if you need form validation
use App\Http\Requests\VPS\ServerRequest as StoreRequest;
use App\Http\Requests\VPS\ServerRequest as UpdateRequest;
use Backpack\CRUD\CrudPanel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Prologue\Alerts\Facades\Alert;

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
                'priority' => 1,
            ],
            [
                // 1-n relationship
                'name' => 'account_id', // the column that contains the ID of that connected entity;
                'label' => "Account", // Table column heading
                'type' => "select",
                'entity' => 'account', // the method that defines the relationship in your Model
                'attribute' => "account", // foreign key attribute that is shown to user
                'model' => "App\Models\VPS\Account", // foreign key model
            ],
            [
                'name' => "end_date", // The db column name
                'label' => "Rend Due Date", // Table column heading
                'type' => "datetime-null",
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
            [  // Select2
                'name' => 'account_id', // the db column for the foreign key
                'label' => "Account",
                'type' => 'select2-notnull',
                'entity' => 'account', // the method that defines the relationship in your Model
                'attribute' => 'account', // foreign key attribute that is shown to user
                'model' => 'App\Models\VPS\Account', // foreign key model
                'addition' => []
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
                'type' => 'text',
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

        $this->crud->addButtonFromView('line', 'stats', 'server-stats', 'beginning');

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

    /**
     * 伺服器狀態頁
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     * @author Jeff Lin
     */
    public function stats(Request $request)
    {
        $server = Server::find($request->server_id);
        if ( !$server) {
            Alert::error("Server dose not exists. <br/> Please check the server's setting.")->flash();
            return redirect()->back();
        }
        $username = 'root';
        $password = $server->ssh_pwd;
        $ip = $server->ip;
        $port = $server->ssh_port;
        $data = array();
        $data['ip'] = $ip;
        $data['server_id'] = $server->id;

        try {
            $connection = ssh2_connect($ip, $port);
            ssh2_auth_password($connection, $username, $password);

            $stream = ssh2_exec($connection, 'docker ps -a');
            stream_set_blocking($stream, true);
            $docker_ps_output = stream_get_contents($stream);

            $stream = ssh2_exec($connection, 'docker stats --no-stream');
            stream_set_blocking($stream, true);
            $docker_stats_output = stream_get_contents($stream);

            ssh2_exec($connection, 'exit');
            unset($connection);

        } catch (\Exception $exception) {
            Log::error('SSH connnect failed. error: '.$exception->getMessage());
            Alert::error("SSH connect failed. <br/> Please check the server's setting.")->flash();
            return redirect()->back();
        }

        $docker_ps = explode("\n", $docker_ps_output);
        foreach ($docker_ps as $key => $value) {
            if ($key >= 1 && !empty($value)) {
                $ps = array_values(array_filter(explode(' ', $value)));
                $name = end($ps);
                // Docker Name 個位數補零
                if (substr($name, 6) < 10 && substr($name, 6, 1) != '0') {
                    $name = substr_replace($name, '0', 6, 0);
                }
                if ($ps[7] == 'Up' OR $ps[8] == 'Up') {  //Docker啟用狀態
                    $status = '';
                    $i = 7;
                    while($i != (count($ps)-2)) {
                        $status .= $ps[$i].' ';
                        $i++;
                    }
                    $docker = array(
                        'container_id' => $ps[0],
                        'created' => $ps[4].' '.$ps[5].' '.$ps[6],
                        'status' => $status,
                        'port' => substr($ps[$i], 8, 4),
                        'name' => $name,
                    );
                } else {  //Docker停用狀態
                    $status = '';
                    $i = 7;
                    while($i != (count($ps)-1)) {
                        $status .= $ps[$i].' ';
                        $i++;
                    }
                    $docker = array(
                        'container_id' => $ps[0],
                        'created' => $ps[4].' '.$ps[5].' '.$ps[6],
                        'status' => $status,
                        'port' => '-',
                        'name' => $name,
                    );
                }

                $data['dockers'][] = $docker;
            }
        }

        $docker_stats = explode("\n", $docker_stats_output);
        foreach ($docker_stats as $key => $value) {
            if ($key >= 1 && !empty($value)) {
                $stats = array_values(array_filter(explode(' ', $value)));
                foreach ($data['dockers'] as &$docker) {
                    if ($stats[0] == $docker['container_id']) {
                        $docker['cpu'] = $stats[2];
                        $docker['mem'] = $stats[3].' '.$stats[4].' '.$stats[5];
                        $docker['net'] = $stats[7];
                    }
                }
                unset($docker);
            }
        }

        return view('vps.server.stats', $data);
    }

    /**
     * 啟動Docker
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @author Jeff Lin
     */
    public function dockerStart(Request $request)
    {
        $server = Server::find($request->server_id);
        if ( !$server) {
            Alert::error("Server dose not exists. <br/> Please check the server's setting.")->flash();
            return redirect()->back();
        }
        $username = 'root';
        $password = $server->ssh_pwd;
        $ip = $server->ip;
        $port = $server->ssh_port;
        $container_id = $request->container_id;

        try {
            $connection = ssh2_connect($ip, $port);
            ssh2_auth_password($connection, $username, $password);

            ssh2_exec($connection, 'docker start '.$container_id);

            ssh2_exec($connection, 'exit');
            unset($connection);

        } catch (\Exception $exception) {
            Log::error('Docker start failed. error: '.$exception->getMessage());
            Alert::error("Docker start failed. <br/> Please check the server's setting.")->flash();
            return redirect()->back();
        }

        Alert::success("Docker start success!")->flash();
        return redirect()->back();
    }

    /**
     * 關閉Docker
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @author Jeff Lin
     */
    public function dockerStop(Request $request)
    {
        $server = Server::find($request->server_id);
        if ( !$server) {
            Alert::error("Server dose not exists. <br/> Please check the server's setting.")->flash();
            return redirect()->back();
        }
        $username = 'root';
        $password = $server->ssh_pwd;
        $ip = $server->ip;
        $port = $server->ssh_port;
        $container_id = $request->container_id;

        try {
            $connection = ssh2_connect($ip, $port);
            ssh2_auth_password($connection, $username, $password);

            ssh2_exec($connection, 'docker stop '.$container_id);

            ssh2_exec($connection, 'exit');
            unset($connection);

        } catch (\Exception $exception) {
            Log::error('Docker stop failed. error: '.$exception->getMessage());
            Alert::error("Docker stop failed. <br/> Please check the server's setting.")->flash();
            return redirect()->back();
        }

        Alert::success("Docker stop success!")->flash();
        return redirect()->back();
    }
}
