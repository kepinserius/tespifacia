<?php

namespace App\Imports;

use App\Models\Project;
use App\Models\Category;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Carbon;

class ProjectsImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Find category by name
        $category = null;
        if (isset($row['category'])) {
            $category = Category::where('name', $row['category'])->first();
        }

        return new Project([
            'category_id' => $category ? $category->id : null,
            'name' => $row['name'],
            'description' => $row['description'] ?? null,
            'is_active' => strtolower($row['is_active']) === 'yes' || $row['is_active'] === '1' || strtolower($row['is_active']) === 'true',
            'metadata' => json_encode(['imported' => true, 'import_date' => now()->toDateTimeString()]),
            'start_date' => isset($row['start_date']) ? Carbon::parse($row['start_date']) : null,
            'end_date' => isset($row['end_date']) ? Carbon::parse($row['end_date']) : null,
        ]);
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'category' => 'required|string|exists:categories,name',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ];
    }
}
