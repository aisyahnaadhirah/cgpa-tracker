<div>
    <h1 class="text-3xl font-bold text-indigo-700">CGPA Tracker</h1>
    <p class="mt-2 text-slate-600">Rekod keputusan semester dan pantau CGPA anda</p>

    <div class="mt-6 rounded-xl bg-indigo-50 p-5">
        <h2 class="text-sm font-medium text-indigo-700">CGPA Keseluruhan</h2>
        <p class="mt-2 text-3xl font-bold text-indigo-900">
            {{ $cgpa === null ? 'Belum ada CGPA' : number_format($cgpa, 2) }}
        </p>

        <p class="mt-2 text-sm text-indigo-700">
            Berdasarkan percubaan pada semester terbaru bagi setiap kod kursus.
        </p>
    </div>

    @if (session()->has('success'))
        <p role="status" class="mt-4 rounded-lg bg-green-50 p-3 text-green-700">
            {{ session('success') }}
        </p>
    @endif
        
    <button
        type="button"
        wire:click="$toggle('showSemesterForm')"
        class="mt-6 rounded-lg bg-indigo-600 px-4 py-2 text-white">
        {{ $showSemesterForm ? 'Tutup Borang' : 'Tambah Semester' }}
    </button>

    @if ($showSemesterForm)
        <form wire:submit="saveSemester" class="mt-4 space-y-4 rounded-lg bg-white p-6">
            <div>
                <label for="academic_year" class="block font-medium">
                    Tahun Akademik
                </label>
                <input
                    id="academic_year"
                    type="text"
                    wire:model="academic_year"
                    placeholder="2026/2027"
                    required
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">

                @error('academic_year')
                    <p class="mt-1 text-sm text-red-600">{{$message}}</p>
                @enderror
            </div>
            <div>
                <label for="semester_number" class="block font-medium">
                    Nombor Semester
                </label>
                <select
                    id="semester_number"
                    wire:model="semester_number"
                    required
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                    <option value="">Pilih semester</option>
                    @for ($number = 1; $number <= 8; $number++)
                        <option value="{{ $number }}">Semester {{ $number }}</option>
                    @endfor
                </select>

                @error('semester_number')
                    <p class="mt-1 text-sm text-red-600">{{$message}}</p>                    
                @enderror
            </div>

            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="saveSemester"
                class="rounded-lg bg-indigo-600 px-4 py-2 text-white disabled:opacity-50">
                Save Semester
            </button>
        </form>
    @endif

    <section class="mt-8">
        <h2 class="text-xl font-semibold">Senarai Semester</h2>
        <div class="mt-4 space-y-3">
            @forelse ($semesters as $semester) <!--untuk papar semua sem-->
                <div
                    wire:key="semester-{{ $semester->id }}"
                    class="rounded-lg border border-slate-200 bg-white p-4">
                    <h3 class="font-semibold text-indigo-700">{{$semester->name}}</h3>

                    <p class="mt-1 text-sm text-slate-600">
                        Tahun Akademik: {{ $semester->academic_year }}
                    </p>

                    @php
                        //panggil pengiraan GPA dari Models
                        $gpa = $semester->gpa();
                    @endphp

                    <p class="mt-2 font-semibold text-indigo-700">
                        GPA:
                        {{ $gpa === null ? 'Belum ada GPA' : number_format($gpa, 2) }}
                    </p>

                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-100">
                                <tr>
                                    <th scope="col" class="px-3 py-2">Course Code</th>
                                    <th scope="col" class="px-3 py-2">Course Name</th>
                                    <th scope="col" class="px-3 py-2">Credit Hours</th>
                                    <th scope="col" class="px-3 py-2">Grade Point</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($semester->courses as $course)
                                    <tr
                                        wire:key="course={{ $course->id }}"
                                        class="border-b border-slate-200">
                                        <td class="px-3 py-2">{{ $course->code }}</td>
                                        <td class="px-3 py-2">{{ $course->name }}</td>
                                        <td class="px-3 py-2">{{ $course->credit_hours }}</td>
                                        <td class="px-3 py-2">{{ number_format((float) $course->grade_point, 2) }}</td> <!--untuk papar dua tempat perpuluhan-->
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-3 py-4 text-slate-500">Belum ada kursus untuk semester ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <button
                        type="button"
                        wire:click="openCourseForm({{ $semester->id }})"
                        class="mt-3 rounded-lg bg-indigo-600 px-4 py-2 text-white">
                        Add Course
                    </button>

                    @if ($selectedSemesterId === (int) $semester->id)
                        <form
                            wire:submit="saveCourse"
                            wire:key="course-form-{{ $semester->id }}"
                            class="mt-4 space-y-4 border-t border-slate-200 pt-4">

                            <div>
                                <label for="course_code" class="block font-medium">Course Code</label>
                                <input 
                                    id="course_code"
                                    type="text"
                                    wire:model="course_code"
                                    maxlength="20"
                                    required
                                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                                @error('course_code')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="course_name" class="block font-medium">Course Name</label>
                                <input 
                                    id="course_name"
                                    type="text"
                                    wire:model="course_name"
                                    maxlength="150"
                                    required
                                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                                @error('course_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="credit_hours" class="block font-medium">Credit Hours</label>
                                <input 
                                    id="credit_hours"
                                    type="number"
                                    wire:model="credit_hours"
                                    min="1"
                                    max="65535"
                                    step="1"
                                    required
                                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                                @error('credit_hours')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="grade_point" class="block font-medium">Grade Point</label>
                                <input 
                                    id="grade_point"
                                    type="number"
                                    wire:model="grade_point"
                                    min="0"
                                    max="4"
                                    step="0.01"
                                    required
                                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                                @error('grade_point')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <button
                                type="submit"
                                wire:loading.attr="disabled"
                                wire:target="saveCourse"
                                class="rounded-lg bg-indigo-600 px-4 py-2 text-white disabled:opacity-50">
                                Save Course
                            </button>
                            <button 
                                type="button"
                                wire:click="$set('selectedSemesterId', null)"
                                class="rounded-lg bg-slate-200 px-4 py-2">
                                Cancel
                            </button>
                        </form>
                    @endif
                </div>
            @empty
                <p class="text-slate-500">
                    Belum ada semester. Klik Tambah Semester.
                </p>
            @endforelse
        </div>
    </section>

    <form method="POST" action="{{ route('logout') }}" class="mt-6">
        @csrf
        <button
            type="submit"
            class="rounded-lg bg-slate-800 px-4 py-2 text-white hover:bg-slate-700">
            Log Out
        </button>
    </form>
</div>
