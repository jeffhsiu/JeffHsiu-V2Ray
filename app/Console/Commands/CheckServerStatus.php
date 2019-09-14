<?php

namespace App\Console\Commands;

use App\Models\Order\Order;
use App\Models\VPS\Server;
use Illuminate\Console\Command;

class CheckServerStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'server:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Server status.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $disable_servers = Server::where('status', Server::STATUS_DISABLE)
			->get();

        foreach ($disable_servers as $server) {
        	$orders = $server->orders()->where('status', Order::STATUS_ENABLE);
        	foreach ($orders->get() as $order) {
        		echo '不可用的伺服器訂單設為 Disable, Order ID: '.$order->id.PHP_EOL;
			}
			$orders->update(['status' => Order::STATUS_DISABLE]);
		}
    }
}
