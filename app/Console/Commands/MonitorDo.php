<?php

namespace App\Console\Commands;

use App\Services\DoServices;
use App\Services\FirebaseServices;
use App\Services\TelegramServices;
use Carbon\Carbon;
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
        $last_error =  $session['last_error'] ?? now();
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
                        $this->info($message);
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
                        FirebaseServices::send('⚠️ Ada DO Baru!, ' . ($value['name'] ?? '-'), $message);
                        TelegramServices::sendToGroup('⚠️ Ada DO Baru!, ' . ($value['name'] ?? '-') . $message);
                    }
                }
            } else {
                $this->info('⚠️ No DO! ');
            }
            $this->saveSession($odoo_length, $last_error);
        } catch (Exception $th) {
            $this->saveSession($session['length'], now());
            if (Carbon::parse($last_error)->diffInMinutes(now()) >= 30) {
                // $this->error($th->getMessage());
                TelegramServices::sendToGroup('Error : ' . $th->getMessage() . ' Last Error : ' . $last_error);
            }
        }
    }

    public function readSession()
    {
        $session_file = storage_path('app/monitor.json');
        if (file_exists($session_file)) {
            $json =  json_decode(File::get($session_file), true);
            if (empty($json['last_error']) || empty($json['length'])) {
                return $this->saveDefaultSession();
            }
            return $json;
        } else {
            return $this->saveDefaultSession();
        }
    }

    public function saveSession($length = 0, $last_error)
    {
        $json = json_encode([
            'length'        => $length,
            'last_error'    => $last_error,
        ], JSON_PRETTY_PRINT);
        $session_file = storage_path('app/monitor.json');
        File::put($session_file, $json);
    }

    public function saveDefaultSession()
    {
        $data = [
            'length'        => 0,
            'last_error'    => Carbon::now()->subHour(),
        ];
        $session_file = storage_path('app/monitor.json');
        File::put($session_file, json_encode($data, JSON_PRETTY_PRINT));
        return $data;
    }
}
