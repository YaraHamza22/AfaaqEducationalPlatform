<?php

namespace Modules\UserMangementModule\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Modules\UserMangementModule\DTOs\AuditorDTO;
use Modules\UserMangementModule\Http\Requests\Api\V1\Auditor\AuditorFilterRequest;
use Modules\UserMangementModule\Http\Requests\Api\V1\Auditor\AuditorStoreRequest;
use Modules\UserMangementModule\Http\Requests\Api\V1\Auditor\AuditorUpdateRequest;
use Modules\UserMangementModule\Models\User;
use Modules\UserMangementModule\Services\V1\AuditorService;

class AuditorController extends Controller
{
    protected AuditorService $auditorService;

    public function __construct(AuditorService $auditorService)
    {
        $this->auditorService = $auditorService;

        $this->middleware('permission:list-auditors')->only('index');
        $this->middleware('permission:show-auditor')->only('show');
        $this->middleware('permission:create-auditor')->only('store');
        $this->middleware('permission:update-auditor')->only('update');
        $this->middleware('permission:delete-auditor')->only('destroy');
    }

    public function index(AuditorFilterRequest $request)
    {
        $auditors = $this->auditorService->list($request->validated());

        return self::paginated($auditors, 'auditors retrieved successfully');
    }

    public function store(AuditorStoreRequest $request)
    {
        $auditorDTO = AuditorDTO::fromArray($request->validated());
        $auditor = $this->auditorService->create($auditorDTO);

        return self::success($auditor, 'auditor created successfully', 201);
    }

    public function show(int $id)
    {
        $auditor = $this->auditorService->findById($id);

        return self::success($auditor, 'auditor retrieved successfully');
    }

    public function update(AuditorUpdateRequest $request, User $auditor)
    {
        $auditorDTO = AuditorDTO::fromArray($request->validated());
        $auditor = $this->auditorService->update($auditor, $auditorDTO);

        return self::success($auditor, 'auditor updated successfully');
    }

    public function destroy(User $auditor)
    {
        $this->auditorService->delete($auditor);

        return self::success(null, 'auditor deleted successfully');
    }
}