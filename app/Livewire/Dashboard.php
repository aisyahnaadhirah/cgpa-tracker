<?php

namespace App\Livewire;

use App\Actions\CreateCourseAction;
use App\Actions\DeleteCourseAction;
use App\Actions\UpdateCourseAction;
use App\Models\Course;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Dashboard extends Component
{
    public string $academic_year = '';

    public string $semester_number = '';

    public bool $showSemesterForm = false;

    public ?int $selectedSemesterId = null;

    public ?int $editingCourseId = null;

    public string $courseSemesterId = '';

    public string $course_code = '';

    public string $course_name = '';

    public string $credit_hours = '';

    public string $grade_point = '';

    protected function rules(): array
    {
        return [
            'academic_year' => [
                'required',
                'regex:/^[0-9]{4}\/[0-9]{4}$/',
            ],  // untuk format tahun jadi 2026/2027
            'semester_number' => [
                'required',
                'integer',
                'between:1,8',
                Rule::unique('semesters', 'semester_number')
                    ->where('user_id', auth()->id()),
            ],  // no semester 1-8 dan pastikan no semester tak digunakan lagi oleh user yang tengah login ni
        ];
    }

    public function saveSemester(): void
    {
        $validated = $this->validate();

        auth()->user()->semesters()->create([
            'name' => 'Semester '.(int) $validated['semester_number'],
            'academic_year' => $validated['academic_year'],
            'semester_number' => (int) $validated['semester_number'],
        ]);

        $this->reset('academic_year', 'semester_number', 'showSemesterForm');

        session()->flash('success', 'Semester berjaya ditambah.');
    }

    public function openCourseForm(int $semesterId): void
    {
        $semester = auth()->user()
            ->semesters()
            ->findOrFail($semesterId);

        $this->reset(
            'editingCourseId',
            'courseSemesterId',
            'course_code',
            'course_name',
            'credit_hours',
            'grade_point'
        );

        $this->resetValidation();

        $this->selectedSemesterId = (int) $semester->id;
        $this->courseSemesterId = (string) $semester->id;
    }

    public function openEditCourse(int $courseId): void
    {
        $course = Course::query()
            ->whereHas('semester', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->findOrFail($courseId);

        $this->resetValidation();

        $this->editingCourseId = (int) $course->id;
        $this->selectedSemesterId = (int) $course->semester_id;
        $this->courseSemesterId = (string) $course->semester_id;

        $this->course_code = $course->code;
        $this->course_name = $course->name;
        $this->credit_hours = (string) $course->credit_hours;
        $this->grade_point = (string) $course->grade_point;
    }

    public function saveCourse(CreateCourseAction $createCourse, UpdateCourseAction $updateCourse): void
    {
        $this->validate([
            'courseSemesterId' => ['required', 'integer'],
        ]);

        $semester = auth()->user()
            ->semesters()
            ->findOrFail($this->courseSemesterId);

        $course = null;

        if ($this->editingCourseId !== null) {
            $course = Course::query()
                ->whereHas('semester', function ($query) {
                    $query->where('user_id', auth()->id());
                })
                ->findOrFail($this->editingCourseId);
        }

        if ($course !== null) {  // UPDATE

            try {
                $course = $updateCourse->handle($course, $semester, [
                    'code' => $this->course_code,
                    'name' => $this->course_name,
                    'credit_hours' => $this->credit_hours,
                    'grade_point' => $this->grade_point,
                ]);
            } catch (ValidationException $exception) {
                $fieldNames = [
                    'code' => 'course_code',
                    'name' => 'course_name',
                ];
                $errors = [];

                foreach ($exception->errors() as $field => $messages) {
                    $errors[$fieldNames[$field] ?? $field] = $messages;
                }

                throw ValidationException::withMessages($errors);
            }
            $message = 'Kursus berjaya dikemas kini.';

        } else { // CREATE
            try {
                $course = $createCourse->handle($semester, [
                    'code' => $this->course_code,
                    'name' => $this->course_name,
                    'credit_hours' => $this->credit_hours,
                    'grade_point' => $this->grade_point,
                ]);
            } catch (ValidationException $exception) {
                $fieldNames = [
                    'code' => 'course_code',
                    'name' => 'course_name',
                ];
                $errors = [];

                foreach ($exception->errors() as $field => $messages) {
                    $errors[$fieldNames[$field] ?? $field] = $messages;
                }

                throw ValidationException::withMessages($errors);
            }

            $message = 'Kursus berjaya ditambah.';
        }

        // dedua guna untuk reset borang dan papar mesej success
        $this->reset(
            'selectedSemesterId',
            'editingCourseId',
            'courseSemesterId',
            'course_code',
            'course_name',
            'credit_hours',
            'grade_point'
        );

        session()->flash('success', $message);
    }

    public function deleteCourse(DeleteCourseAction $deleteCourse, int $courseId): void
    {
        $course = Course::query()
            ->whereHas('semester', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->findOrFail($courseId);

        $deleteCourse->handle($course);

        if ($this->editingCourseId === (int) $course->id) {
            $this->reset(
                'selectedSemesterId',
                'editingCourseId',
                'courseSemesterId',
                'course_code',
                'course_name',
                'credit_hours',
                'grade_point'
            );
            $this->resetValidation();
        }
        session()->flash('success', 'Kursus berjaya dipadam.');
    }

    public function render()
    {
        return view('livewire.dashboard', [
            'semesters' => auth()->user()
                ->semesters()
                ->with('courses')
                ->orderBy('semester_number')
                ->get(),
            'cgpa' => Cache::remember(
                'cgpa_'.auth()->id(),
                60,
                function () {
                    return auth()->user()->cgpa();
                }
            ),
        ])->layout('layouts.app');  // susun ikut semester
    }
}
