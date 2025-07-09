<?php

namespace App\Console\Commands;

use App\Services\DoServices;
use App\Services\FirebaseServices;
use App\Services\TelegramServices;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class MonitorDo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:monitor-do';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $session = $this->readSession();
        // $this->info(json_encode($session));
        try {
            $do = DoServices::getAll('CENT/OUT/', 5);
            $odoo_length = $do['length'];
            $old_length = $session['length'];
            $this->info('old: ' . $old_length);
            $this->info('new: ' . $odoo_length);
            if ($odoo_length > $old_length && $odoo_length > 0) {
                $selisih = $odoo_length - $old_length;
                $this->info('selisih: ' . $selisih);
                if ($selisih > 5) {
                    if ($old_length > 0) {
                        $title = '⚠️ Ada ' . $selisih . 'DO Baru!';
                        $message = $title;
                        FirebaseServices::send($title, $message);
                        TelegramServices::sendToGroup($message);
                    } else {
                        TelegramServices::sendToGroup('Program Started!');
                        $this->info('Program Started');
                    }
                } else {
                    $this->info('Jumlah Berubah. Kirim Notif!');
                    for ($i = 0; $i < $selisih; $i++) {
                        $value = $do['records'][$i];
                        $message = ' SO : ' . ($value['origin'] ?? '') . ', ';
                        $message .= ' TO : ' . get_name($value['partner_id']) . ', ';
                        $message .= ' NOTE : ' . ($value['note_to_wh'] ?? '') . ', ';
                        // $this->info('⚠️ Ada DO!, ' . $value['origin'] . $message);
                        FirebaseServices::send('⚠️ Ada DO Baru!, ' . $value['name'], $message);
                        TelegramServices::sendToGroup('⚠️ Ada DO Baru!, ' . $value['name'] . $message);
                    }
                }
            } else {
                $this->info('⚠️ No DO! ');
            }
            $this->saveSession($odoo_length, 0);
        } catch (\Throwable $th) {
            $this->saveSession($session['length'], $session['error'] + 1);
            if ($session['error'] < 3) {
                $this->error($th->getMessage());
                TelegramServices::sendToGroup('Error : ' . $th->getMessage());
            }
        }
    }

    public function readSession()
    {
        $session_file = storage_path('app/monitor.json');
        if (file_exists($session_file)) {
            $json = File::get($session_file);
            return json_decode($json, true);
        } else {
            $data = [
                'length'    => 0,
                'error'     => 0
            ];
            File::put($session_file, json_encode($data, JSON_PRETTY_PRINT));
            return $data;
        }
    }

    public function saveSession($length = 0, $error = 0)
    {
        $json = json_encode(['length' => $length, 'error' => $error], JSON_PRETTY_PRINT);
        $session_file = storage_path('app/monitor.json');
        File::put($session_file, $json);
    }
}
