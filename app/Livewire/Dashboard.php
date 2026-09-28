<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Cache;
use App\Events\CourseCreated;

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

    protected function rules(): array {
        return [
            'academic_year' => [
                'required',
                'regex:/^[0-9]{4}\/[0-9]{4}$/', 
            ],  //untuk format tahun jadi 2026/2027
            'semester_number' => [
                'required',
                'integer',
                'between:1,8',
                \Illuminate\Validation\Rule::unique('semesters', 'semester_number')
                    ->where('user_id', auth()->id()),
            ],  //no semester 1-8 dan pastikan no semester tak digunakan lagi oleh user yang tengah login ni
        ];
    }

    public function saveSemester(): void {
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
        $course = \App\Models\Course::query()
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

    public function saveCourse(): void
    {
        $this->validate([
            'courseSemesterId' => ['required', 'integer'],
        ]);

        $semester = auth()->user()
            ->semesters()
            ->findOrFail($this->courseSemesterId);

        $course = null;

        if ($this->editingCourseId !== null) {
            $course = \App\Models\Course::query()
                ->whereHas('semester', function ($query) {
                    $query->where('user_id', auth()->id());
                })
                ->findOrFail($this->editingCourseId);
        }

        $this->course_code = strtoupper(trim($this->course_code));
        $this->course_name = trim($this->course_name);

        $uniqueCode = \Illuminate\Validation\Rule::unique('courses', 'code')
            ->where('semester_id', $semester->id);

        if ($course !== null) {
            $uniqueCode->ignore($course);
        }

        $validated = $this->validate([
            'course_code' => ['required', 'string', 'max:20', $uniqueCode],
            'course_name' => ['required', 'string', 'max:150'],
            'credit_hours' => ['required', 'integer', 'min:1', 'max:65535'],
            'grade_point' => [
                'required', 'numeric', 'between:0,4', 'decimal:0,2',
            ],
        ]);

        $data = [
            'code' => $validated['course_code'],
            'name' => $validated['course_name'],
            'credit_hours' => (int) $validated['credit_hours'],
            'grade_point' => $validated['grade_point'],
        ];

        if ($course !== null) {
            $course->fill($data);
            $course->semester()->associate($semester);
            $course->save();

            Cache::forget('cgpa_' . auth()->id());

            $message = 'Kursus berjaya dikemas kini.';
        } else {
            $course = $semester->courses()->create($data);

            CourseCreated::dispatch($course);

            $message = 'Kursus berjaya ditambah.';
        }

        

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

    public function deleteCourse(int $courseId): void {
        $course = \App\Models\Course::query()
            ->whereHas('semester', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->findOrFail($courseId);

        $course->delete();

        Cache::forget('cgpa_' . auth()->id());

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
                'cgpa_' . auth()->id(),
                60,
                function () {
                    return auth()->user()->cgpa();
                }
            ),
        ])->layout('layouts.app');  //susun ikut semester
    }
}
