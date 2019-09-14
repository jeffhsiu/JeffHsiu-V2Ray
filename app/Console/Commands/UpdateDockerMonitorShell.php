<?php

namespace App\Console\Commands;

use App\Models\VPS\Server;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateDockerMonitorShell extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shell:update-monitor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update servers docker monitor shell.';

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

                echo "Updating docker monitor shell...".PHP_EOL;
                $stream = ssh2_exec($connection, 'wget --no-cache -O /etc/v2ray/docker-monitor.sh https://git.io/fjnF8 2>&1');
                stream_set_blocking($stream, true);
                $stream_out = ssh2_fetch_stream($stream, SSH2_STREAM_STDIO);
                echo stream_get_contents($stream_out);
                fclose($stream);
                echo "Update success.".PHP_EOL.PHP_EOL;

                ssh2_exec($connection, 'exit');
                unset($connection);

            } catch (\Exception $exception) {
                Log::error('Update shell failed. error: '.$exception->getMessage());
                continue;
            }
        }
    }
}
