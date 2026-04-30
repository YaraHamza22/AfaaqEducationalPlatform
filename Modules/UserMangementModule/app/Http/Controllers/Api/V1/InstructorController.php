<?php

namespace Modules\UserMangementModule\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\UserMangementModule\Services\V1\InstructorService;
use Modules\UserMangementModule\Http\Requests\Api\V1\Instructor\InstructorFilterRequest;
use Modules\UserMangementModule\Http\Requests\Api\V1\Instructor\InstructorStoreRequest;
use Modules\UserMangementModule\Http\Requests\Api\V1\Instructor\InstructorUpdateRequest;
use Modules\UserMangementModule\Models\User;

class InstructorController extends Controller
{
    protected InstructorService $instructorService;

    public function __construct(InstructorService $instructorService)
    {
        $this->instructorService = $instructorService;

        $this->middleware('permission:list-instructors')->only('index');
        $this->middleware('permission:show-instructor')->only('show');
        $this->middleware('permission:create-instructor')->only('store');
        $this->middleware('permission:update-instructor')->only('update');
        $this->middleware('permission:delete-instructor')->only('destroy');
    }

    public function index(InstructorFilterRequest $request)
    {
        $instructors = $this->instructorService->list($request->validated());

        return self::paginated($instructors, 'instructors retrieved successfully');
    }
    public function store(InstructorStoreRequest $request)
    {
        $instructor = $this->instructorService->create($request->validated());

        return self::success($instructor, 'instructor created successfully', 201);
    }
    public function show(int $id)
    {
        $instructor = $this->instructorService->findById($id);

        return self::success($instructor);
    }
    public function update(InstructorUpdateRequest $request, User $instructor)
    {
        $instructor = $this->instructorService->update($instructor, $request->validated());

        return self::success($instructor, 'instructor updated successfully');
    }
    public function destroy(User $instructor)
    {       
        $this->instructorService->delete($instructor);

        return self::success(null, 'instructor deleted successfully');
    }

   
}
