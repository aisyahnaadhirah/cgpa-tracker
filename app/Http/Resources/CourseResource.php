<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

    //response untuk tentukan JSon hanya boleh tunjuk field yang disenaraikan dalam file ni sahaja
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'semester_id' => (int) $this->semester_id,
            'code' => $this->code,
            'name' => $this->name,
            'credit_hours' => (int) $this->credit_hours,
            'grade_point' => (float) $this->grade_point,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
