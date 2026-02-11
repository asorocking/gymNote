<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserSetting;
use App\Models\TrackerRecord;
use inertia\Inertia;

class TrackerController extends Controller
{
    public function index(Request $request)
    {
        // Get date from request or current
        $date = $request->input('date', now()->format('Y-m-d'));

        return Inertia::render('gymNote', [
           'initialRecords' => TrackerRecord::where('date_key', $date)
                ->orderBy('created_at', 'desc')
                ->get(),
            'settings' => UserSetting::first()?->settings ?? [],
            'current_day' => $date
        ]);
    }

    public function update(Request $request, TrackerRecord $record)
    {
//        if ($record->user_id != auth()->id()) {
//            return response()->json(['error' => 'Unauthorized'], 403);
//        }

        $record->update($request->only([
            'val1', 'val2', 'val3', 'weight', 'description', 'notes', 'is_completed'
        ]));

        return back()->with('status', 'saved');
    }

    public function store(Request $request)
    {
        // Важно: берем дату, которую прислал фронт
        $record = \App\Models\TrackerRecord::create([
            'id' => (string)(microtime(true) * 10000),
            'mode' => $request->mode,
            'date_key' => $request->input('date_key') ?: now()->format('Y-m-d'),
            'description' => '',
            'notes' => '',
            'is_completed' => false,
            'sort_order' => 0,
        ]);

        // Inertia автоматически перезагрузит данные через index()
        return back();
    }

    public function destroy(\App\Models\TrackerRecord $record)
    {
        $record->delete();

        return back();
    }

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
            ['settings' => [
                'exerciseRegistry' => $data['exerciseRegistry'] ?? [],
                'shopListRegistry' => $data['shopListRegistry'] ?? [],
                'workoutSessions' => $data['workoutSessions'] ?? [],
            ]]
        );

        // 2. Сохраняем записи (Records)
        foreach ($data['records'] as $record) {
            TrackerRecord::updateOrCreate(
                ['id' => (string)$record['id']], // Принудительно к строке
                [
                    'mode' => $record['mode'] ?? 'gym',
                    'date_key' => $record['dateKey'],
                    'description' => $record['description'] ?? '',
                    'notes',
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
