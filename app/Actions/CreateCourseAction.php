<?php

namespace App\Actions;

use App\Events\CourseCreated;
use App\Models\Course;
use App\Models\Semester;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CreateCourseAction
{
    /**
     * @param array{
     *     code?: mixed,
     *     name?: mixed,
     *     credit_hours?: mixed,
     *     grade_point?: mixed
     * } $data
     */

    public function handle(Semester $semester, array $data): Course {

        if (is_string($data['code'] ?? null)) {
            $data['code'] = strtoupper(trim($data['code']));
        }

        if (is_string($data['name'] ?? null)) {
            $data['name'] = trim($data['name']);
        }

        $validated = Validator::make($data, [
            'code' => [
                'bail',
                'required',
                'string',
                'max:20',
                Rule::unique('courses', 'code')
                    ->where('semester_id', $semester->id),
            ],
            'name' => ['required', 'string', 'max:150'],
            'credit_hours' => ['required', 'integer', 'min:1', 'max:65535'],
            'grade_point' => [
                'required', 'numeric', 'between:0,4', 'decimal:0,2',
            ],
        ])->validate();

        $validated['credit_hours'] = (int) $validated['credit_hours']; //pastikan credit hours memang dalam integer

        $course = $semester->courses()->create($validated);

        CourseCreated::dispatch($course);

        return $course;
    }
}
