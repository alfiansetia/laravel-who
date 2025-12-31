<?php

namespace App\Console\Commands;

use App\Services\DoServices;
use App\Services\FirebaseServices;
use App\Services\TelegramServices;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
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
        $last_error = Arr::get($session, 'last_error', now());

        try {
            $do = DoServices::getAll('CENT/OUT/', 5);

            // Validasi response dari DoServices
            if (!is_array($do)) {
                throw new Exception('DoServices response tidak valid');
            }

            $odoo_length = Arr::get($do, 'length', 0);
            $old_length = Arr::get($session, 'length', 0);

            $this->info('old: ' . $old_length);
            $this->info('new: ' . $odoo_length);

            if ($odoo_length > $old_length && $odoo_length > 0) {
                $selisih = $odoo_length - $old_length;
                $this->info('selisih: ' . $selisih);

                if ($selisih > 5) {
                    if ($old_length > 0) {
                        $title = '⚠️ Ada ' . $selisih . ' DO Baru!';
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
                    $records = Arr::get($do, 'records', []);

                    if (!is_array($records)) {
                        throw new Exception('DO records tidak valid');
                    }

                    for ($i = 0; $i < $selisih; $i++) {
                        if (!isset($records[$i])) {
                            continue; // Skip jika record tidak ada
                        }

                        $value = $records[$i];
                        $message = ' SO : ' . Arr::get($value, 'origin', '') . ', ';
                        $message .= ' TO : ' . get_name($value['partner_id'] ?? null) . ', ';
                        $message .= ' NOTE : ' . Arr::get($value, 'note_to_wh', '') . ', ';

                        $doName = Arr::get($value, 'name', '-');
                        $so_id = Arr::get($value, 'sale_id.0', '0');
                        FirebaseServices::send('⚠️ Ada DO Baru!, ' . $doName, $message, $so_id);
                        TelegramServices::sendToGroup('⚠️ Ada DO Baru!, ' . $doName . $message);
                    }
                }
            } else {
                $this->info('⚠️ No DO! ');
            }

            $this->saveSession($odoo_length, $last_error);
        } catch (Exception $th) {
            $this->saveSession(Arr::get($session, 'length', 0), now());

            if (Carbon::parse($last_error)->diffInMinutes(now()) >= 30) {
                TelegramServices::sendToGroup('Error : ' . $th->getMessage() . ' Last Error : ' . $last_error);
            }

            Log::error('MonitorDo: Exception', [
                'error' => $th->getMessage(),
                'last_error' => $last_error
            ]);
        }
    }

    public function readSession()
    {
        try {
            $session_file = storage_path('app/monitor.json');

            if (file_exists($session_file)) {
                $json = File::get($session_file);
                $data = json_decode($json, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::warning('MonitorDo: JSON tidak valid', [
                        'error' => json_last_error_msg()
                    ]);
                    return $this->saveDefaultSession();
                }

                if (empty($data['last_error']) || !isset($data['length'])) {
                    return $this->saveDefaultSession();
                }

                return $data;
            } else {
                return $this->saveDefaultSession();
            }
        } catch (Exception $e) {
            Log::error('MonitorDo: Exception saat membaca session', [
                'error' => $e->getMessage()
            ]);
            return $this->saveDefaultSession();
        }
    }

    public function saveSession($length = 0, $last_error)
    {
        try {
            $json = json_encode([
                'length'        => $length,
                'last_error'    => $last_error,
            ], JSON_PRETTY_PRINT);

            $session_file = storage_path('app/monitor.json');
            File::put($session_file, $json);
        } catch (Exception $e) {
            Log::error('MonitorDo: Exception saat menyimpan session', [
                'error' => $e->getMessage()
            ]);
        }
    }

    public function saveDefaultSession()
    {
        try {
            $data = [
                'length'        => 0,
                'last_error'    => Carbon::now()->subHour(),
            ];

            $session_file = storage_path('app/monitor.json');
            File::put($session_file, json_encode($data, JSON_PRETTY_PRINT));

            return $data;
        } catch (Exception $e) {
            Log::error('MonitorDo: Exception saat menyimpan default session', [
                'error' => $e->getMessage()
            ]);

            return [
                'length' => 0,
                'last_error' => Carbon::now()->subHour(),
            ];
        }
    }
}
