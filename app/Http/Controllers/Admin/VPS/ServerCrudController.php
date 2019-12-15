<?php

namespace App\Http\Controllers\Admin\VPS;

use App\Models\Order\Order;
use App\Models\VPS\Server;
use App\Models\VPS\ServerLog;
use Backpack\CRUD\app\Http\Controllers\CrudController;

// VALIDATION: change the requests to match your own file names if you need form validation
use App\Http\Requests\VPS\ServerRequest as StoreRequest;
use App\Http\Requests\VPS\ServerRequest as UpdateRequest;
use Backpack\CRUD\CrudPanel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
                'options' => Server::getProvidersMap()
            ],
			[
				'name' => 'status',
				'label' => 'Status',
				'type' => 'select_from_array',
				'options' => [
					Server::STATUS_ENABLE => 'Enable',
					Server::STATUS_DISABLE => '<span class="text-red text-bold">Disable</span>',
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
            [
                'name' => 'ws_host', // The db column name
                'label' => 'WS Host', // Table column heading
                'type' => 'text',
            ],
            [
                'name' => 'remark', // The db column name
                'label' => 'Remark', // Table column heading
                'type' => 'text',
            ],
        ]);

        $this->crud->addFields([
            [
                'name' => 'provider',
                'label' => 'Provider',
                'type' => 'select2_from_array',
                'options' => Server::getProvidersMap()
            ],
			[
				'name' => 'status',
				'label' => 'Status',
				'type' => 'radio',
				'options' => [
					Server::STATUS_ENABLE => 'Enable',
					Server::STATUS_DISABLE => 'Disable',
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
                'options'   => (function ($query) {
                    return $query->orderBy('id', 'desc')->get();
                })
            ],
            [   // Number
                'name' => 'ssh_port',
                'label' => 'SSH Port',
                'type' => 'number',
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
            [
                'name' => 'ws_host', // The db column name
                'label' => 'WS Host', // Table column heading
                'type' => 'text',
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
            'name' => 'provider',
            'label'=> 'Provider',
            'type' => 'dropdown',
        ], Server::getProvidersMap(),
            function($value) { // if the filter is active
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

        if ( !auth()->user()->can('vps-servers-sshpwd')) {
            $this->crud->removeField('ssh_pwd');
        }
        if ( !auth()->user()->can('vps-servers-create')) {
            $this->crud->denyAccess('create');
        }
        if ( !auth()->user()->can('vps-servers-update')) {
            $this->crud->denyAccess('update');
        }
        if ( !auth()->user()->can('vps-servers-delete')) {
            $this->crud->denyAccess('delete');
        }

        $this->crud->orderBy('id', 'desc');
        $this->crud->orderBy('status', 'asc');

        // add asterisk for fields that are required in ServerRequest
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
		if ($server->status == Server::STATUS_DISABLE) {
			Alert::error("Server disabled. <br/> Can not connect...")->flash();
			return redirect()->back();
		}
        $username = 'root';
        $password = $server->ssh_pwd;
        $ip = $server->ip;
        $port = $server->ssh_port;
        $data = array();
        $data['ip'] = $ip;
        $data['ws_host'] = $server->ws_host;
        $data['provider'] = Server::getProvidersMap()[$server->provider];
        $data['server_id'] = $server->id;
        $data['end_date'] = strstr($server->end_date, ' ', true);
        $data['remark'] = $server->remark;

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

        // 獲取docker ps
        $docker_ps = explode("\n", $docker_ps_output);
        foreach ($docker_ps as $key => $value) {
            if ($key >= 1 && !empty($value)) {
                $ps = array_values(array_filter(explode(' ', $value)));
                $name = end($ps);
                // Docker Name 個位數補零
                if (substr($name, 6) < 10 && substr($name, 6, 1) != '0') {
                    $name = substr_replace($name, '0', 6, 0);
                }
                if (in_array('Up', $ps)) {  //Docker啟用狀態
                    $created = '';
                    $i = 4;
                    $end = array_search('Up', $ps);
                    while($i != $end) {
                        $created .= $ps[$i].' ';
                        $i++;
                    }

                    $status = '';
                    $i = array_search('Up', $ps);
                    $end = count($ps) - 2;
                    while($i != $end) {
                        $status .= $ps[$i].' ';
                        $i++;
                    }

                    $docker = array(
                        'container_id' => $ps[0],
                        'created' => $created,
                        'status' => $status,
                        'port' => substr($ps[$i],strpos($ps[$i],':')+1,strpos($ps[$i],'-')-strpos($ps[$i],':')-1),
                        'name' => $name,
                    );
                } else {  //Docker停用狀態
                    $created = '';
                    $i = 4;
                    $end = array_search('Exited', $ps);
                    while($i != $end) {
                        $created .= $ps[$i].' ';
                        $i++;
                    }

                    $status = '';
                    $i = array_search('Exited', $ps);
                    $end = count($ps) - 1;
                    while($i != $end) {
                        $status .= $ps[$i].' ';
                        $i++;
                    }

                    $docker = array(
                        'container_id' => $ps[0],
                        'created' => $created,
                        'status' => $status,
                        'port' => '-',
                        'name' => $name,
                    );
                }

                $data['dockers'][] = $docker;
            }
        }

        // 獲取docker stats
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

        // 對應的訂單記錄
        foreach ($data['dockers'] as &$docker) {
            $order = Order::where('server_id', $server->id)
                ->where('docker_name', $docker['name'])
                ->whereIn('status', [Order::STATUS_ENABLE, Order::STATUS_EXPIRED])
                ->orderBy('end_date', 'desc')
                ->first();

            $is_end = $order ? Carbon::today()->gt(Carbon::parse($order->end_date)) : false;
            $docker['order'] = $order;
            $docker['is_end'] = $is_end;

            $server_log = ServerLog::where('server_id', $server->id)
                ->where('docker_name', $docker['name'])
                ->whereIn('action', [ServerLog::ACTION_DOCKER_STOP, ServerLog::ACTION_DOCKER_REDO, ServerLog::ACTION_DOCKER_RESTART])
                ->where('order_id', $order ? $order->id : 0)
                ->orderBy('id', 'desc')
                ->first();
            $docker['net_last'] = $server_log ? $server_log->net : null;
        }

        return view('admin.vps.server.stats', $data);
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
        $order = Order::where('server_id', $request->server_id)
            ->where('docker_name', $request->docker_name)
            ->whereIn('status', [Order::STATUS_ENABLE, Order::STATUS_EXPIRED])
            ->orderBy('end_date', 'desc')
            ->first();

        // 供應商只能操作自己的訂單
        if (auth()->user()->hasRole('Distributor')) {
            if ( !$order || auth()->user()->distributor->id != $order->distributor_id) {
                return abort(403);
            }
        }

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

        // 伺服器操作記錄
        ServerLog::create([
            'user_id' => auth()->id(),
            'server_id' => $request->server_id,
            'order_id' => $order ? $order->id : 0,
            'ip' => $server->ip,
            'docker_name' => $request->docker_name,
            'action' => ServerLog::ACTION_DOCKER_START,
            'reason' => 'Manual'
        ]);

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
        $order = Order::where('server_id', $request->server_id)
            ->where('docker_name', $request->docker_name)
            ->whereIn('status', [Order::STATUS_ENABLE, Order::STATUS_EXPIRED])
            ->orderBy('end_date', 'desc')
            ->first();

        // 供應商只能操作自己的訂單
        if (auth()->user()->hasRole('Distributor')) {
            if ( !$order || auth()->user()->distributor->id != $order->distributor_id) {
                return abort(403);
            }
        }

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

            $stream = ssh2_exec($connection, 'docker stats --no-stream --format "{{.NetIO}}" '.$container_id);
            stream_set_blocking($stream, true);
            $docker_stats_output = stream_get_contents($stream);
            $net = explode(' ', $docker_stats_output)[0];
            ssh2_exec($connection, 'docker stop '.$container_id);

            ssh2_exec($connection, 'exit');
            unset($connection);

        } catch (\Exception $exception) {
            Log::error('Docker stop failed. error: '.$exception->getMessage());
            Alert::error("Docker stop failed. <br/> Please check the server's setting.")->flash();
            return redirect()->back();
        }

        // 伺服器操作記錄
        ServerLog::create([
            'user_id' => auth()->id(),
            'server_id' => $request->server_id,
            'order_id' => $order ? $order->id : 0,
            'ip' => $server->ip,
            'docker_name' => $request->docker_name,
            'action' => ServerLog::ACTION_DOCKER_STOP,
            'reason' => 'Manual',
            'net' => $net
        ]);

        Alert::success("Docker stop success!")->flash();
        return redirect()->back();
    }

    /**
     * 重刷Docker
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @author Jeff Lin
     */
    public function dockerRedo(Request $request)
    {
        $order = Order::where('server_id', $request->server_id)
            ->where('docker_name', $request->docker_name)
            ->whereIn('status', [Order::STATUS_ENABLE, Order::STATUS_EXPIRED])
            ->orderBy('end_date', 'desc')
            ->first();

        // 分銷商只能操作自己的訂單
        if (auth()->user()->hasRole('Distributor')) {
            if ( !$order || auth()->user()->distributor->id != $order->distributor_id) {
                return abort(403);
            }
        }

        $server = Server::find($request->server_id);
        if ( !$server) {
            Alert::error("Server dose not exists. <br/> Please check the server's setting.")->flash();
            return redirect()->back();
        }

        $shell = storage_path('v2ray/shell/v2ray-config.sh');
        $ip = $server->ip;
        $ssh_port = $server->ssh_port;
        $ssh_user = 'root';
        $ssh_pwd = $server->ssh_pwd;
        $index = substr($request->docker_name, -2);
        $host = $server->ws_host;
        $begin_port = 15550;
        // 部分不能改防火牆的server
        if (in_array($server->account->account, config('account.diff_port'))) {
            $begin_port = 5550;
        }
        $port = $request->port ?: $begin_port + $index;
		if ($port < 1024 || $port > 65535) {
			Alert::error("Port can only be set between 1024 ~ 65535.")->flash();
			return redirect()->back();
		}

        $container_id = $request->container_id;
        $path = storage_path("v2ray/account/$ip");
        $command = "bash $shell $ip $port $index $path $host";

        try {
            shell_exec("$command 2>&1");

            $connection = ssh2_connect($ip, $ssh_port);
            ssh2_auth_password($connection, $ssh_user, $ssh_pwd);

            $stream = ssh2_exec($connection, 'docker stats --no-stream --format "{{.NetIO}}" '.$container_id);
            stream_set_blocking($stream, true);
            $docker_stats_output = stream_get_contents($stream);
            $net = explode(' ', $docker_stats_output)[0];

            // scp v2ray config
            ssh2_scp_send($connection,
                '/usr/local/etc/v2ray/config.json',
                '/etc/v2ray/config-'.$index.'.json');

            // remove docker and exec docker run command
            ssh2_exec($connection, 'docker rm -f v2ray-'.$index.' v2ray-'.($index+0));
            ssh2_exec($connection, "docker run -d --name=v2ray-$index -v /etc/v2ray:/etc/v2ray \
            -p $port:$port --memory=80M --restart=always v2ray/official  \
            v2ray -config=/etc/v2ray/config-$index.json");

            ssh2_exec($connection, 'exit');
            unset($connection);

            // rm local config file
            shell_exec("rm -f /usr/local/etc/v2ray/vmess_qr.json /usr/local/etc/v2ray/config.json 2>&1");

        } catch (\Exception $exception) {
            shell_exec("rm -f /usr/local/etc/v2ray/vmess_qr.json /usr/local/etc/v2ray/config.json");
            Log::error('Docker redo failed. error: '.$exception->getMessage());
            Alert::error("Docker redo failed. <br/> See the detail from log.")->flash();
            return redirect()->back();
        }

        // Disable關聯的訂單
        Order::where('server_id', $server->id)
            ->where('docker_name', $request->docker_name)
            ->update(['status' => Order::STATUS_DISABLE]);

        // 伺服器操作記錄
        ServerLog::create([
            'user_id' => auth()->id(),
            'server_id' => $request->server_id,
            'order_id' => $order ? $order->id : 0,
            'ip' => $server->ip,
            'docker_name' => $request->docker_name,
            'action' => ServerLog::ACTION_DOCKER_REDO,
            'reason' => 'Manual',
            'net' => $net
        ]);

        Alert::success("Docker redo success!")->flash();
        return redirect()->back();
    }

    /**
     * 取得Docker V2Ray config
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     * @author Jeff Lin
     */
    public function getV2RayConfig(Request $request)
    {
        // 供應商只能查看自己的訂單
        if (auth()->user()->hasRole('Distributor')) {
            $order = Order::where('server_id', $request->server_id)
                ->where('docker_name', $request->docker_name)
                ->whereIn('status', [Order::STATUS_ENABLE, Order::STATUS_EXPIRED])
                ->orderBy('end_date', 'desc')
                ->first();
            if ( !$order || auth()->user()->distributor->id != $order->distributor_id) {
                return abort(403);
            }
        }

        $server = Server::find($request->server_id);
        if ( !$server) {
            Alert::error("Server dose not exists. <br/> Please check the server's setting.")->flash();
            return redirect()->back();
        }
        $path = storage_path('v2ray/account');
        $index = substr($request->docker_name, -2, 2);
        switch ($request->type) {
            case 'txt':
                $path = $path.'/'.$server->ip.'/config-'.$index.'.txt';
                break;
            case 'qrcode':
                $path = $path.'/'.$server->ip.'/qrcode-'.$index.'.png';
                break;
            default:
                Alert::error("Config type error.")->flash();
                return redirect()->back();
                break;
        }

        $headers = ['Cache-Control' => 'no-cache'];
        return response()->file($path, $headers);
    }

    /**
     * Server order list page
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @author Jeff Lin
     */
    public function serverOrderList(Request $request)
    {
        $data = array();
        $servers = array();
        $paginate = Server::selectRaw('server.id AS server_id, COUNT(server.id) as count')
            ->leftJoin(DB::raw('(SELECT distinct docker_name, server_id FROM `order` WHERE status IN ('.Order::STATUS_ENABLE.','.Order::STATUS_EXPIRED.')) AS o '),
                'server.id', '=', 'o.server_id')
			->where('server.status', Server::STATUS_ENABLE)
            ->groupBy('server.id')
            ->orderBy('count')
            ->paginate(10);

        foreach ($paginate as $item) {
            $server = Server::find($item->server_id);
            $dockers = array();
            for ($i = 1; $i <= 10; $i++) {
                $docker_name = 'v2ray-'.str_pad($i,2,"0",STR_PAD_LEFT);
                $order = Order::where('server_id', $server->id)
                    ->where('docker_name', $docker_name)
                    ->whereIn('status', [Order::STATUS_ENABLE, Order::STATUS_EXPIRED])
                    ->orderBy('end_date', 'desc')
                    ->first();
                $dockers[$i]['name'] = $docker_name;
                $dockers[$i]['order'] = $order;

                $is_end = $order ? Carbon::today()->gt(Carbon::parse($order->end_date)) : false;
                $dockers[$i]['is_end'] = $is_end;
            }
            $server->dockers = $dockers;
            $servers[] = $server;
        }

        $data['paginate'] = $paginate;
        $data['servers'] = $servers;

        return view('admin.vps.server.order-list', $data);
    }
}
