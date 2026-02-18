<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SystemSettingsController extends Controller
{
    public function index()
    {
        $maintenanceMode = DB::table('settings')->where('key', 'maintenance_mode')->first();
        $maintenanceMessage = DB::table('settings')->where('key', 'maintenance_message')->first();

        return view('admin.system-settings', [
            'maintenanceMode' => $maintenanceMode ? $maintenanceMode->value : '0',
            'maintenanceMessage' => $maintenanceMessage ? $maintenanceMessage->value : '',
        ]);
    }

    public function updateMaintenance(Request $request)
    {
        $request->validate([
            'maintenance_mode' => 'required|in:0,1',
            'maintenance_message' => 'nullable|string',
        ]);

        DB::table('settings')->updateOrInsert(
            ['key' => 'maintenance_mode'],
            ['value' => $request->maintenance_mode, 'type' => 'boolean', 'group' => 'maintenance']
        );

        DB::table('settings')->updateOrInsert(
            ['key' => 'maintenance_message'],
            ['value' => $request->maintenance_message, 'type' => 'string', 'group' => 'maintenance']
        );

        $status = $request->maintenance_mode === '1' ? 'ativado' : 'desativado';
        
        return redirect()->route('admin.system-settings')
            ->with('success', "Modo de manutenção {$status} com sucesso!");
    }
}
