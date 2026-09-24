<?php

namespace App\Livewire;

use Livewire\Component;

class Dashboard extends Component
{
    public string $academic_year = '';
    public string $semester_number = '';
    public bool $showSemesterForm = false;
    public ?int $selectedSemesterId = null;
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

    public function openCourseForm(int $semesterId): void {
        $semester = auth()->user()
            ->semesters()
            ->findOrFail($semesterId);

        $this->reset(
            'course_code',
            'course_name',
            'credit_hours',
            'grade_point'
        );

        $this->resetValidation();
        $this->selectedSemesterId = (int) $semester->id;
    }

    public function saveCourse(): void {
        $semester = auth()->user()
            ->semesters()
            ->findOrFail($this->selectedSemesterId);

        $this->course_code = strtoupper(trim($this->course_code));
        $this->course_name = trim($this->course_name);

        $validated = $this->validate([
            'course_code' => [
                'required',
                'string',
                'max:20',
                \Illuminate\Validation\Rule::unique('courses', 'code')
                    ->where('semester_id', $semester->id),
            ],
            'course_name' => ['required', 'string', 'max:150'],
            'credit_hours' => ['required', 'integer', 'min:1', 'max:65535'],
            'grade_point' => [
                'required', 'numeric', 'between:0,4', 'decimal:0,2',
            ],
        ]);  //untuk check rules data yang user masukkan

        $semester->courses()->create([
            'code' => $validated['course_code'],
            'name' => $validated['course_name'],
            'credit_hours' => (int) $validated['credit_hours'],
            'grade_point' => $validated['grade_point'],
        ]);

        $this->reset(
            'selectedSemesterId',
            'course_code',
            'course_name',
            'credit_hours',
            'grade_point'
        );

        session()->flash('success', 'Course berjaya ditambah.');
    }

    public function render()
    {
        return view('livewire.dashboard', [
            'semesters' => auth()->user()
                ->semesters()
                ->with('courses')
                ->orderBy('semester_number')
                ->get(),
        ])->layout('layouts.app');  //susun ikut semester
    }
}
