<?php

namespace Modules\CommunicationModule\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\CommunicationModule\Jobs\ProcessIntegrationWebhook;
use Modules\CommunicationModule\Http\Requests\VirtualSession\StoreAttendanceRequest;
use Modules\CommunicationModule\Http\Requests\VirtualSession\StoreVirtualSessionRequest;
use Modules\CommunicationModule\Http\Requests\VirtualSession\UpdateVirtualSessionRequest;
use Modules\CommunicationModule\Models\VirtualSession;
use Modules\CommunicationModule\Services\V1\IntegrationService;
use Throwable;

class VirtualSessionController extends Controller
{
    public function __construct(private IntegrationService $integrationService)
    {
    }

    public function index()
    {
        $sessions = VirtualSession::query()->latest()->paginate(15);
        return self::paginated($sessions, 'Virtual sessions fetched successfully.');
    }

    public function store(StoreVirtualSessionRequest $request)
    {
        $session = VirtualSession::query()->create($request->validated() + [
            'host_id' => Auth::id(),
            'status' => 'draft',
        ]);
        return self::success($session, 'Virtual session created successfully.', 201);
    }

    public function show(VirtualSession $virtualSession)
    {
        return self::success($virtualSession, 'Virtual session fetched successfully.');
    }

    public function update(UpdateVirtualSessionRequest $request, VirtualSession $virtualSession)
    {
        $this->authorize('update', $virtualSession);
        $virtualSession->update($request->validated());
        return self::success($virtualSession->fresh(), 'Virtual session updated successfully.');
    }

    public function destroy(VirtualSession $virtualSession)
    {
        $this->authorize('delete', $virtualSession);
        $virtualSession->delete();
        return self::success(null, 'Virtual session deleted successfully.');
    }

    public function publish(VirtualSession $virtualSession)
    {
        $this->authorize('update', $virtualSession);
        $published = $this->integrationService->publishSession($virtualSession);
        return self::success($published, 'Virtual session published successfully.');
    }

    public function cancel(VirtualSession $virtualSession)
    {
        $this->authorize('update', $virtualSession);
        $cancelled = $this->integrationService->cancelSession($virtualSession);
        return self::success($cancelled, 'Virtual session cancelled successfully.');
    }

    public function storeAttendance(StoreAttendanceRequest $request, VirtualSession $virtualSession)
    {
        $this->authorize('update', $virtualSession);
        $attendance = $this->integrationService->storeAttendance($virtualSession, $request->validated());
        return self::success($attendance, 'Session attendance stored successfully.', 201);
    }

    public function webhook(Request $request, string $provider): JsonResponse
    {
        try {
            $signature = (string) $request->header('X-Signature');
            if (! $signature) {
                return self::error('Missing webhook signature.', 422);
            }

            $secret = (string) config("communicationmodule.webhooks.{$provider}.secret");
            if ($secret === '') {
                return self::error('Webhook secret not configured for provider.', 500);
            }

            $payload = $request->getContent();
            $expected = hash_hmac('sha256', $payload, $secret);
            $normalized = str_starts_with($signature, 'sha256=')
                ? substr($signature, 7)
                : $signature;

            if (! hash_equals($expected, $normalized)) {
                return self::error('Invalid webhook signature.', 401);
            }

            $payloadData = (array) $request->json()->all();
            ProcessIntegrationWebhook::dispatch($provider, $payloadData);

            return self::success([
                'provider' => $provider,
                'received' => true,
                'event' => $request->input('event'),
            ], 'Webhook payload accepted.');
        } catch (Throwable $e) {
            return self::error('Webhook processing failed.', 422, $e->getMessage());
        }
    }
}
