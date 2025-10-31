<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FonnteService
{
    protected $apiUrl;
    protected $token;
    protected $adminPhone;

    public function __construct()
    {
        $this->apiUrl = config('services.fonnte.api_url');
        $this->token = config('services.fonnte.token');
        $this->adminPhone = config('services.fonnte.admin_phone');
    }

    /**
     * Kirim pesan WhatsApp
     */
    public function sendMessage(string $phone, string $message): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => $this->token,
            ])->post($this->apiUrl, [
                'target' => $phone,
                'message' => $message,
                'countryCode' => '62', // Indonesia
            ]);

            $result = $response->json();

            if ($response->successful() && isset($result['status']) && $result['status'] == 'success') {
                Log::info('Fonnte message sent successfully', [
                    'phone' => $phone,
                    'message' => $message,
                    'response' => $result
                ]);
                return ['success' => true, 'data' => $result];
            } else {
                Log::error('Fonnte message failed', [
                    'phone' => $phone,
                    'message' => $message,
                    'response' => $result
                ]);
                return ['success' => false, 'error' => $result['message'] ?? 'Unknown error'];
            }
        } catch (\Exception $e) {
            Log::error('Fonnte API exception', [
                'phone' => $phone,
                'message' => $message,
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Kirim notifikasi low stock ke admin
     */
    public function sendLowStockNotification(array $lowStockItems): array
    {
        if (empty($lowStockItems)) {
            return ['success' => true, 'message' => 'No low stock items to notify'];
        }

        $message = $this->buildLowStockMessage($lowStockItems);
        
        return $this->sendMessage($this->adminPhone, $message);
    }

    /**
     * Buat pesan notifikasi low stock
     */
    protected function buildLowStockMessage(array $lowStockItems): string
    {
        $totalItems = count($lowStockItems);
        $timestamp = now()->format('d/m/Y H:i');
        
        $message = "🚨 *NOTIFIKASI STOK RENDAH* 🚨\n\n";
        $message .= "Tanggal: {$timestamp}\n";
        $message .= "Total item stok rendah: {$totalItems}\n\n";
        $message .= "📋 *Detail Item:*\n";
        
        foreach ($lowStockItems as $index => $item) {
            $no = $index + 1;
            $status = $item['current_stock'] <= 0 ? 'HABIS' : 'MENIPIS';
            $emoji = $item['current_stock'] <= 0 ? '❌' : '⚠️';
            
            $message .= "\n{$no}. {$emoji} *{$item['item_name']}*\n";
            $message .= "   SKU: {$item['sku']}\n";
            $message .= "   Stok: {$item['current_stock']} {$item['unit']}\n";
            $message .= "   Minimum: {$item['low_stock_threshold']} {$item['unit']}\n";
            $message .= "   Status: {$status}\n";
            $message .= "   Kategori: {$item['category']}\n";
            if (!empty($item['supplier'])) {
                $message .= "   Supplier: {$item['supplier']}\n";
            }
        }
        
        $message .= "\n💡 *Rekomendasi:*\n";
        $message .= "• Segera lakukan pembelian ulang\n";
        $message .= "• Cek transaksi terakhir item tersebut\n";
        $message .= "• Update stok jika ada perubahan\n\n";
        
        $message .= "🔗 Login ke sistem: " . route('dashboard') . "\n\n";
        $message .= "_Notifikasi otomatis dari Chicking BJM_";
        
        return $message;
    }

    /**
     * Kirim notifikasi custom
     */
    public function sendCustomNotification(string $message, string $phone = null): array
    {
        $targetPhone = $phone ?: $this->adminPhone;
        return $this->sendMessage($targetPhone, $message);
    }

    /**
     * Cek status API
     */
    public function checkStatus(): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => $this->token,
            ])->get('https://api.fonnte.com/device');

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            } else {
                return ['success' => false, 'error' => 'API request failed'];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Schedule notifikasi low stock setiap hari jam 11 siang
     */
    public function scheduleDailyLowStockAt11AM(): array
    {
        try {
            // Set waktu schedule untuk hari ini jam 11:00 atau besok jika sudah lewat
            $now = Carbon::now();
            $scheduleTime = $now->format('Y-m-d') . ' 11:00:00';
            
            // Jika sudah lewat jam 11, schedule untuk besok
            if ($now->format('H:i') >= '11:00') {
                $scheduleTime = $now->addDay()->format('Y-m-d') . ' 11:00:00';
            }

            // Template message untuk low stock
            $message = "🚨 *NOTIFIKASI STOK RENDAH HARIAN* 🚨\n\n";
            $message .= "Waktu: Setiap hari pukul 11:00\n";
            $message .= "Status: Notifikasi otomatis aktif\n\n";
            $message .= "📋 *Item dengan stok rendah akan dilaporkan di sini*\n\n";
            $message .= "🔗 Login ke sistem: " . route('dashboard') . "\n\n";
            $message .= "_Notifikasi otomatis dari Chicking BJM_";

            $response = Http::withHeaders([
                'Authorization' => $this->token,
            ])->post($this->apiUrl, [
                'target' => $this->adminPhone,
                'message' => $message,
                'schedule' => $scheduleTime,
                'countryCode' => '62',
                'repeat' => 'daily',
                'repeat_count' => 30 // Repeat selama 30 hari
            ]);

            $result = $response->json();

            if ($response->successful() && isset($result['status']) && $result['status'] == 'success') {
                Log::info('Daily low stock notification scheduled at 11 AM', [
                    'schedule_time' => $scheduleTime,
                    'response' => $result
                ]);
                return ['success' => true, 'data' => $result, 'schedule_time' => $scheduleTime];
            } else {
                Log::error('Failed to schedule daily low stock notification', [
                    'schedule_time' => $scheduleTime,
                    'response' => $result
                ]);
                return ['success' => false, 'error' => $result['message'] ?? 'Unknown error'];
            }
        } catch (\Exception $e) {
            Log::error('Schedule daily low stock exception', [
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Cek apakah sudah ada schedule low stock harian
     */
    public function hasDailyLowStockSchedule(): bool
    {
        try {
            $scheduled = $this->getScheduledMessages();
            
            if ($scheduled['success'] && isset($scheduled['data'])) {
                foreach ($scheduled['data'] as $item) {
                    // Cek apakah ada schedule dengan pesan low stock harian
                    if (strpos($item['message'] ?? '', 'NOTIFIKASI STOK RENDAH HARIAN') !== false) {
                        return true;
                    }
                }
            }
            
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get daftar pesan yang dijadwalkan
     */
    public function getScheduledMessages(): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => $this->token,
            ])->get('https://api.fonnte.com/schedule');

            if ($response->successful()) {
                $result = $response->json();
                return ['success' => true, 'data' => $result];
            } else {
                return ['success' => false, 'error' => 'Failed to fetch scheduled messages'];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Cancel semua schedule low stock harian
     */
    public function cancelDailyLowStockSchedules(): array
    {
        try {
            $scheduled = $this->getScheduledMessages();
            $cancelled = 0;
            
            if ($scheduled['success'] && isset($scheduled['data'])) {
                foreach ($scheduled['data'] as $item) {
                    // Cancel schedule dengan pesan low stock harian
                    if (strpos($item['message'] ?? '', 'NOTIFIKASI STOK RENDAH HARIAN') !== false) {
                        $this->cancelScheduledMessage($item['id']);
                        $cancelled++;
                    }
                }
            }
            
            return ['success' => true, 'cancelled' => $cancelled];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Cancel pesan yang dijadwalkan berdasarkan ID
     */
    public function cancelScheduledMessage(string $scheduleId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => $this->token,
            ])->delete('https://api.fonnte.com/schedule/' . $scheduleId);

            $result = $response->json();

            if ($response->successful() && isset($result['status']) && $result['status'] == 'success') {
                Log::info('Scheduled message cancelled', ['schedule_id' => $scheduleId]);
                return ['success' => true, 'data' => $result];
            } else {
                Log::error('Failed to cancel scheduled message', [
                    'schedule_id' => $scheduleId,
                    'response' => $result
                ]);
                return ['success' => false, 'error' => $result['message'] ?? 'Unknown error'];
            }
        } catch (\Exception $e) {
            Log::error('Cancel scheduled message exception', [
                'schedule_id' => $scheduleId,
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}