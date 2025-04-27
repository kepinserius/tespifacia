<?php

namespace App\Exports;

use App\Models\Project;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProjectsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Project::with('category')->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'UUID',
            'Category',
            'Name',
            'Description',
            'Is Active',
            'Document Path',
            'Start Date',
            'End Date',
            'Created At',
            'Updated At'
        ];
    }

    /**
     * @param mixed $project
     * @return array
     */
    public function map($project): array
    {
        return [
            $project->id,
            $project->uuid,
            $project->category->name,
            $project->name,
            $project->description,
            $project->is_active ? 'Yes' : 'No',
            $project->document_path,
            $project->start_date ? $project->start_date->format('Y-m-d H:i:s') : '',
            $project->end_date ? $project->end_date->format('Y-m-d H:i:s') : '',
            $project->created_at->format('Y-m-d H:i:s'),
            $project->updated_at->format('Y-m-d H:i:s')
        ];
    }
}
