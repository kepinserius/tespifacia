<?php

namespace App\Imports;

use App\Models\Task;
use App\Models\Project;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Carbon;

class TasksImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Find project by name
        $project = null;
        if (isset($row['project'])) {
            $project = Project::where('name', $row['project'])->first();
        }

        return new Task([
            'project_id' => $project ? $project->id : null,
            'title' => $row['title'],
            'description' => $row['description'] ?? null,
            'status' => $row['status'] ?? 'pending',
            'is_priority' => strtolower($row['is_priority']) === 'yes' || $row['is_priority'] === '1' || strtolower($row['is_priority']) === 'true',
            'metadata' => json_encode(['imported' => true, 'import_date' => now()->toDateTimeString()]),
            'due_date' => isset($row['due_date']) ? Carbon::parse($row['due_date']) : null,
        ]);
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'project' => 'required|string|exists:projects,name',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:pending,in_progress,completed,cancelled',
            'is_priority' => 'nullable',
            'due_date' => 'nullable|date',
        ];
    }
}
