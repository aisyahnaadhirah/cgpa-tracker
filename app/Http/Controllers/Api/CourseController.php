<?php

namespace App\Http\Controllers\Api;

use App\Actions\CreateCourseAction;
use App\Actions\DeleteCourseAction;
use App\Actions\UpdateCourseAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CourseController extends Controller
{
    // akan terima request dari route, dapatkan course melalui model then pulangkan json
    public function index(Request $request): JsonResponse
    {
        $user = $request->user(); // dapatkan user yang login

        abort_if($user === null, 401); // stopkan request kalau takde user

        $courses = Course::query()
            ->whereHas('semester', function (Builder $query) use ($user): void { // amik course yang semester dia user yang login tu punya
                $query->where('user_id', $user->id);
            })
            ->orderBy('id') // sort by id user yang login (dalam oracle)
            ->get(); // jalankan query dan amik data hasil dari oracle

        return CourseResource::collection($courses)->response();
    }

    public function show(Request $request, int $courseId): JsonResponse
    {
        $user = $request->user();

        abort_if($user === null, 401);

        $course = Course::query()
            ->whereHas('semester', function (Builder $query) use ($user): void {
                $query->where('user_id', $user->id);
            })
            ->findOrFail($courseId);

        return (new CourseResource($course))->response();
    }

    // untuk create course through API
    public function store(Request $request, CreateCourseAction $createCourse): JsonResponse
    {
        $user = $request->user();

        abort_if($user === null, 401);

        // check semester id, kena required dan mesti int
        $validated = $request->validate([
            'semester_id' => ['required', 'integer'],
        ]);

        // cari sem berdasarkan semester id tapi cari semester yang user yang login ni sahaja
        $semester = $user->semesters()
            ->findOrFail($validated['semester_id']);

        // call CreateCourseAction
        $course = $createCourse->handle(
            $semester,
            $request->only([
                'code',
                'name',
                'credit_hours',
                'grade_point',
            ])
        );

        return (new CourseResource($course))
            ->response()
            ->setStatusCode(201);
    }

    public function update(Request $request, UpdateCourseAction $updateCourse, int $courseId): JsonResponse
    {
        $user = $request->user();

        abort_if($user === null, 401);

        $course = Course::query()
            ->whereHas('semester', function (Builder $query) use ($user): void {
                $query->where('user_id', $user->id);
            })
            ->findOrFail($courseId);

        $course = $updateCourse->handle(
            $course,
            $course->semester,
            $request->only([
                'code',
                'name',
                'credit_hours',
                'grade_point',
            ])
        );

        return (new CourseResource($course))->response();
    }

    public function destroy(Request $request, DeleteCourseAction $deleteCourse, int $courseId): Response
    {
        $user = $request->user();

        abort_if($user === null, 401);

        $course = Course::query()
            ->whereHas('semester', function (Builder $query) use ($user): void {
                $query->where('user_id', $user->id);
            })
            ->findOrFail($courseId);

        $deleteCourse->handle($course);

        return response()->noContent();
    }
}
