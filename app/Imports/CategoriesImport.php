<?php

namespace App\Imports;

use App\Models\Category;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Carbon;

class CategoriesImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new Category([
            'name' => $row['name'],
            'description' => $row['description'] ?? null,
            'is_active' => strtolower($row['is_active']) === 'yes' || $row['is_active'] === '1' || strtolower($row['is_active']) === 'true',
            'metadata' => json_encode(['imported' => true, 'import_date' => now()->toDateTimeString()]),
            'published_at' => isset($row['published_at']) ? Carbon::parse($row['published_at']) : null,
        ]);
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable',
            'published_at' => 'nullable|date',
        ];
    }
}
