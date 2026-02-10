<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserSetting;
use App\Models\TrackerRecords;

class TrackerController extends Controller
{
    public function import(Request $request)
    {
        // Проверяем, что файл вообще пришел
        $request->validate([
            'json_file' => 'required|file',
        ]);

        $file = $request->file('json_file');
        $data = json_decode(file_get_contents($file), true);

        // 1. Сохраняем настройки (Реестры)
        UserSetting::updateOrCreate(
            ['user_id' => auth()->id()],
            ['settings' => [
                'exerciseRegistry' => $data['exerciseRegistry'] ?? [],
                'shopListRegistry' => $data['shopListRegistry'] ?? [],
                'workoutSessions' => $data['workoutSessions'] ?? [],
            ]]
        );

        // 2. Сохраняем записи (Records)
        foreach ($data['records'] as $record) {
            TrackerRecords::updateOrCreate(
                ['id' => (string)$record['id']], // Принудительно к строке
                [
                    'user_id' => auth()->id(),
                    'mode' => $record['mode'] ?? 'gym',
                    'date_key' => $record['dateKey'],
                    'description' => $record['description'] ?? '',
                    'val1' => isset($record['val1']) ? (string)$record['val1'] : null,
                    'val2' => isset($record['val2']) ? (string)$record['val2'] : null,
                    'val3' => isset($record['val3']) ? (string)$record['val3'] : null,
                    'weight' => isset($record['weight']) ? (string)$record['weight'] : null,
                    'is_completed' => $record['isCompleted'] ?? false,
                    'sort_order' => $record['sortOrder'] ?? 0,
                ]
            );
        }

        return back()->with('message', 'Данные успешно импортированы!');
    }
}
