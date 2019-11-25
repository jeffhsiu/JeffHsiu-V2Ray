<?php

namespace App\Console\Commands;

use App\Models\VPS\Server;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateSSLCertificate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ssl:certificate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update servers ssl certificate.';

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
        $servers = Server::where('status' , '<>', Server::STATUS_DISABLE)
			->get();

        foreach ($servers as $server) {
            $username = 'root';
            $password = $server->ssh_pwd;
            $ip = $server->ip;
            $port = $server->ssh_port;

            try {
                echo "Connecting $ip ...".PHP_EOL;
                $connection = ssh2_connect($ip, $port);
                ssh2_auth_password($connection, $username, $password);

                echo "Updating ssl certificate...".PHP_EOL;
                ssh2_exec($connection, 'mkdir /etc/v2ray/ssl');
                $result = ssh2_scp_send($connection, '/etc/nginx/ssl/*.jeffhsiu.com_chain.crt', '/etc/v2ray/ssl/*.jeffhsiu.com_chain.crt', 0777);
                $result = ssh2_scp_send($connection, '/etc/nginx/ssl/*.jeffhsiu.com_key.key', '/etc/v2ray/ssl/*.jeffhsiu.com_key.key', 0777);

                echo $result ? "Update success.".PHP_EOL : "Update failed.".PHP_EOL;

                ssh2_exec($connection, 'exit');
                unset($connection);

            } catch (\Exception $exception) {
                echo $exception->getMessage().PHP_EOL;
                Log::error('Updating ssl certificate. error: '.$exception->getMessage());
                continue;
            }
        }
    }
}
