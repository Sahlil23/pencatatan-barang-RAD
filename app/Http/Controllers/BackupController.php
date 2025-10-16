<?php
// app/Http/Controllers/BackupController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use ZipArchive;

class BackupController extends Controller
{
    public function index()
    {
        $backups = $this->getBackupList();
        return view('backup.index', compact('backups'));
    }

    public function create(Request $request)
    {
        try {
            $backupType = $request->get('type', 'full'); // full, data-only, structure-only
            $filename = $this->generateBackupFilename($backupType);
            
            $success = $this->createBackup($filename, $backupType);
            
            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Backup berhasil dibuat!',
                    'filename' => $filename,
                    'download_url' => route('backup.download', ['filename' => $filename])
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Backup gagal dibuat!'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function download($filename)
    {
        $filePath = storage_path('app/backups/' . $filename);
        
        if (!file_exists($filePath)) {
            abort(404, 'Backup file not found');
        }

        return response()->download($filePath, $filename, [
            'Content-Type' => 'application/sql',
        ]);
    }

    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:sql,zip'
        ]);

        try {
            $file = $request->file('backup_file');
            $success = $this->restoreBackup($file);
            
            if ($success) {
                return redirect()->back()->with('success', 'Database berhasil direstore!');
            } else {
                return redirect()->back()->with('error', 'Restore gagal!');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function delete($filename)
    {
        try {
            $filePath = storage_path('app/backups/' . $filename);
            
            if (file_exists($filePath)) {
                unlink($filePath);
                return response()->json([
                    'success' => true,
                    'message' => 'Backup berhasil dihapus!'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'File tidak ditemukan!'
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    private function createBackup($filename, $type = 'full')
    {
        $backupPath = storage_path('app/backups');
        
        // Create backup directory if not exists
        if (!file_exists($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $filePath = $backupPath . '/' . $filename;
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');
        $dbHost = config('database.connections.mysql.host');

        // Build mysqldump command based on type
        switch ($type) {
            case 'structure-only':
                $command = "mysqldump --no-data -h {$dbHost} -u {$dbUser} -p{$dbPass} {$dbName} > {$filePath}";
                break;
            case 'data-only':
                $command = "mysqldump --no-create-info -h {$dbHost} -u {$dbUser} -p{$dbPass} {$dbName} > {$filePath}";
                break;
            default: // full
                $command = "mysqldump -h {$dbHost} -u {$dbUser} -p{$dbPass} {$dbName} > {$filePath}";
                break;
        }

        // Execute mysqldump
        $output = [];
        $returnVar = 0;
        exec($command, $output, $returnVar);

        // Alternative method if mysqldump not available
        if ($returnVar !== 0) {
            return $this->createBackupAlternative($filePath, $type);
        }

        return file_exists($filePath) && filesize($filePath) > 0;
    }

    private function createBackupAlternative($filePath, $type = 'full')
    {
        // Alternative backup using Laravel DB facade
        $tables = DB::select('SHOW TABLES');
        $dbName = config('database.connections.mysql.database');
        
        $sql = "-- Chicking BJM Database Backup\n";
        $sql .= "-- Generated: " . Carbon::now() . "\n";
        $sql .= "-- Database: {$dbName}\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            $tableName = array_values((array) $table)[0];
            
            // Get table structure
            if ($type !== 'data-only') {
                $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`")[0];
                $sql .= "-- Structure for table {$tableName}\n";
                $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
                $sql .= $createTable->{'Create Table'} . ";\n\n";
            }

            // Get table data
            if ($type !== 'structure-only') {
                $rows = DB::table($tableName)->get();
                if ($rows->count() > 0) {
                    $sql .= "-- Data for table {$tableName}\n";
                    $sql .= "INSERT INTO `{$tableName}` VALUES\n";
                    
                    $values = [];
                    foreach ($rows as $row) {
                        $rowData = array_map(function($value) {
                            return is_null($value) ? 'NULL' : "'" . addslashes($value) . "'";
                        }, (array) $row);
                        $values[] = '(' . implode(',', $rowData) . ')';
                    }
                    $sql .= implode(",\n", $values) . ";\n\n";
                }
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        return file_put_contents($filePath, $sql) !== false;
    }

    private function restoreBackup($file)
    {
        $tempPath = $file->store('temp');
        $filePath = storage_path('app/' . $tempPath);

        try {
            $sql = file_get_contents($filePath);
            
            // Split SQL commands
            $commands = array_filter(explode(';', $sql));
            
            DB::beginTransaction();
            
            foreach ($commands as $command) {
                $command = trim($command);
                if (!empty($command)) {
                    DB::unprepared($command);
                }
            }
            
            DB::commit();
            
            // Clean up temp file
            Storage::delete($tempPath);
            
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            Storage::delete($tempPath);
            throw $e;
        }
    }

    private function getBackupList()
    {
        $backupPath = storage_path('app/backups');
        
        if (!file_exists($backupPath)) {
            return collect();
        }

        $files = scandir($backupPath);
        $backups = collect();

        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                $filePath = $backupPath . '/' . $file;
                $backups->push([
                    'filename' => $file,
                    'size' => $this->formatBytes(filesize($filePath)),
                    'created_at' => Carbon::createFromTimestamp(filemtime($filePath)),
                    'type' => $this->getBackupType($file)
                ]);
            }
        }

        return $backups->sortByDesc('created_at');
    }

    private function generateBackupFilename($type = 'full')
    {
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $dbName = config('database.connections.mysql.database');
        return "backup_{$dbName}_{$type}_{$timestamp}.sql";
    }

    private function getBackupType($filename)
    {
        if (strpos($filename, '_structure_') !== false) return 'Structure Only';
        if (strpos($filename, '_data_') !== false) return 'Data Only';
        return 'Full Backup';
    }

    private function formatBytes($size, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, $precision) . ' ' . $units[$i];
    }

    public function schedule()
    {
        // Auto backup harian
        try {
            $filename = $this->generateBackupFilename('auto');
            $success = $this->createBackup($filename);
            
            if ($success) {
                // Hapus backup lama (lebih dari 30 hari)
                $this->cleanOldBackups();
                \Log::info('Auto backup created: ' . $filename);
            }
        } catch (\Exception $e) {
            \Log::error('Auto backup failed: ' . $e->getMessage());
        }
    }

    private function cleanOldBackups($days = 30)
    {
        $backupPath = storage_path('app/backups');
        $files = glob($backupPath . '/backup_*_auto_*.sql');
        $cutoffTime = Carbon::now()->subDays($days)->timestamp;

        foreach ($files as $file) {
            if (filemtime($file) < $cutoffTime) {
                unlink($file);
            }
        }
    }
}