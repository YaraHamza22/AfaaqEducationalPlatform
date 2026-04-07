<?php

namespace Modules\UserMangementModule\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Modules\UserMangementModule\Http\Requests\Api\V1\Student\StudentFilterRequest;
use Modules\UserMangementModule\Http\Requests\Api\V1\Student\StudentStoreRequest;
use Modules\UserMangementModule\Http\Requests\Api\V1\Student\StudentUpdateRequest;
use Modules\UserMangementModule\Models\User;
use Modules\UserMangementModule\Services\V1\StudentService;

class StudentController extends Controller
{
    protected StudentService $studentService;

    public function __construct(StudentService $studentService)
    {
        $this->studentService = $studentService;

        $this->middleware('permission:list-students')->only('index');
        $this->middleware('permission:show-student')->only('show');
        $this->middleware('permission:create-student')->only('store');
        $this->middleware('permission:update-student')->only('update');
        $this->middleware('permission:delete-student')->only('destroy');
    }

    public function index(StudentFilterRequest $request)
    {
        $students = $this->studentService->list($request->validated());

        return self::paginated($students, 'students retrieved successfully');
    }

    public function store(StudentStoreRequest $request)
    {
        $student = $this->studentService->create($request->validated());

        return self::success($student, 'student created successfully', 201);
    }

    public function show(int $id)
    {
        $student = $this->studentService->findById($id);

        return self::success($student);
    }

    public function update(StudentUpdateRequest $request, User $student)
    {
        $student = $this->studentService->update($student, $request->validated());

        return self::success($student, 'student updated successfully');
    }

    public function destroy(User $student)
    {
        $this->studentService->delete($student);

        return self::success(null, 'student deleted successfully');
    }
}