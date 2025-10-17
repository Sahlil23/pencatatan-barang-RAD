<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
}