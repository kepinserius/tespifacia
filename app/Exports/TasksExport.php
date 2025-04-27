<?php

namespace App\Exports;

use App\Models\Task;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class TasksExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Task::with(['project', 'project.category'])->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'UUID',
            'Project',
            'Category',
            'Title',
            'Description',
            'Status',
            'Is Priority',
            'Due Date',
            'Created At',
            'Updated At'
        ];
    }

    /**
     * @param mixed $task
     * @return array
     */
    public function map($task): array
    {
        return [
            $task->id,
            $task->uuid,
            $task->project->name,
            $task->project->category->name,
            $task->title,
            $task->description,
            $task->status,
            $task->is_priority ? 'Yes' : 'No',
            $task->due_date ? $task->due_date->format('Y-m-d H:i:s') : '',
            $task->created_at->format('Y-m-d H:i:s'),
            $task->updated_at->format('Y-m-d H:i:s')
        ];
    }
}
