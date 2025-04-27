<?php

namespace App\Exports;

use App\Models\Category;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class CategoriesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Category::all();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'UUID',
            'Name',
            'Description',
            'Is Active',
            'Published At',
            'Created At',
            'Updated At'
        ];
    }

    /**
     * @param mixed $category
     * @return array
     */
    public function map($category): array
    {
        return [
            $category->id,
            $category->uuid,
            $category->name,
            $category->description,
            $category->is_active ? 'Yes' : 'No',
            $category->published_at ? $category->published_at->format('Y-m-d H:i:s') : '',
            $category->created_at->format('Y-m-d H:i:s'),
            $category->updated_at->format('Y-m-d H:i:s')
        ];
    }
}
