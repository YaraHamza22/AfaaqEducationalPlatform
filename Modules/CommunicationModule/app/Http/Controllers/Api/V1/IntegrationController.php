<?php

namespace Modules\CommunicationModule\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\CommunicationModule\Http\Requests\Integration\StoreIntegrationRequest;
use Modules\CommunicationModule\Models\ExternalIntegration;
use Modules\CommunicationModule\Services\V1\IntegrationService;
use Throwable;

class IntegrationController extends Controller
{
    public function __construct(private IntegrationService $integrationService)
    {
    }

    public function index()
    {
        $rows = ExternalIntegration::query()->where('user_id', Auth::id())->latest()->paginate(15);
        return self::paginated($rows, 'Integrations fetched successfully.');
    }

    public function store(StoreIntegrationRequest $request)
    {
        $row = ExternalIntegration::query()->updateOrCreate(
            ['user_id' => Auth::id(), 'provider' => $request->validated()['provider']],
            $request->validated()
        );
        return self::success($row, 'Integration saved successfully.', 201);
    }

    public function update(StoreIntegrationRequest $request, ExternalIntegration $externalIntegration)
    {
        $this->authorize('update', $externalIntegration);
        $externalIntegration->update($request->validated());
        return self::success($externalIntegration->fresh(), 'Integration updated successfully.');
    }

    public function destroy(ExternalIntegration $externalIntegration)
    {
        $this->authorize('delete', $externalIntegration);
        $externalIntegration->update(['is_active' => false]);
        return self::success(null, 'Integration revoked successfully.');
    }

    public function oauthUrl(string $provider)
    {
        try {
            $result = $this->integrationService->getOAuthRedirectUrl($provider, (int) Auth::id());
            return self::success($result, 'OAuth URL generated successfully.');
        } catch (Throwable $e) {
            return self::error($e->getMessage(), 422);
        }
    }

    public function exchangeCode(Request $request, string $provider)
    {
        $validated = $request->validate([
            'code' => ['required', 'string'],
        ]);

        try {
            $integration = $this->integrationService->exchangeAuthorizationCode(
                $provider,
                (int) Auth::id(),
                $validated['code']
            );

            return self::success($integration, 'Provider connected successfully.', 201);
        } catch (Throwable $e) {
            return self::error($e->getMessage(), 422);
        }
    }
}
