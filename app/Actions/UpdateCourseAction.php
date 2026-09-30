<?php

namespace App\Actions;

use App\Models\Course;
use App\Models\Semester;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UpdateCourseAction
{
    /**
     * @param array{
     *     code?: mixed,
     *     name?: mixed,
     *     credit_hours?: mixed,
     *     grade_point?: mixed
     * } $data
     */
    public function handle(Course $course, Semester $semester, array $data): Course
    {
        // array_replace tu gabungkan nilai asal dengan nilai baru, sebab PATCH ni update sebahagian je so nilai asal still kena ada
        $data = array_replace(
            $course->only([
                'code',
                'name',
                'credit_hours',
                'grade_point',
            ]),
            $data
        );

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
                    ->where('semester_id', $semester->id)
                    ->ignore($course),
            ],
            'name' => ['required', 'string', 'max:150'],
            'credit_hours' => ['required', 'integer', 'min:1', 'max:65535'],
            'grade_point' => [
                'required', 'numeric', 'between:0,4', 'decimal:0,2',
            ],
        ])->validate();

        $validated['credit_hours'] = (int) $validated['credit_hours'];

        $course->fill($validated);
        // associate($semester) tu membolehkan course tu dipindahkan ke semester lain jugak
        $course->semester()->associate($semester);
        $course->save();

        Cache::forget('cgpa_'.$semester->user_id);

        return $course;

    }
}
