<?php

namespace App\Console\Commands;

use App\Models\VPS\Server;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateDockerImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'docker:update-images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update servers docker images.';

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

                echo "Updating docker images...".PHP_EOL;
                $stream = ssh2_exec($connection, 'docker pull v2ray/official');
                stream_set_blocking($stream, true);
                $stream_out = ssh2_fetch_stream($stream, SSH2_STREAM_STDIO);
                echo stream_get_contents($stream_out);
                fclose($stream);
                echo "Update success.".PHP_EOL;

                ssh2_exec($connection, 'exit');
                unset($connection);

            } catch (\Exception $exception) {
                echo $exception->getMessage().PHP_EOL;
                Log::error('Update docker images failed. error: '.$exception->getMessage());
                continue;
            }
        }
    }
}
