<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Log;

/**
 * Trait ApiClient
 *
 * Calls internal API routes (/api/v1/...) using the Sanctum token
 * stored in the session after login.  All data fetching and mutations
 * go through the API — no direct Eloquent queries in the Web layer.
 */
trait ApiClient
{
    // ----------------------------------------------------------------
    // Raw HTTP helpers — always return array, never null
    // ----------------------------------------------------------------

    protected function apiGet(string $endpoint, array $query = []): array
    {
        $query = array_filter($query, fn ($v) => $v !== null && $v !== '');
        return $this->safeJson(
            fn () => $this->dispatchApiRequest('GET', $endpoint, $query),
            "GET {$endpoint}"
        );
    }

    protected function apiPost(string $endpoint, array $data = []): array
    {
        return $this->safeJson(
            fn () => $this->dispatchApiRequest('POST', $endpoint, $data),
            "POST {$endpoint}"
        );
    }

    protected function apiPut(string $endpoint, array $data = []): array
    {
        return $this->safeJson(
            fn () => $this->dispatchApiRequest('PUT', $endpoint, $data),
            "PUT {$endpoint}"
        );
    }

    protected function apiPatch(string $endpoint, array $data = []): array
    {
        return $this->safeJson(
            fn () => $this->dispatchApiRequest('PATCH', $endpoint, $data),
            "PATCH {$endpoint}"
        );
    }

    protected function apiDelete(string $endpoint): array
    {
        return $this->safeJson(
            fn () => $this->dispatchApiRequest('DELETE', $endpoint),
            "DELETE {$endpoint}"
        );
    }

    protected function dispatchApiRequest(string $method, string $endpoint, array $data = [])
    {
        $uri = '/api/v1/' . ltrim($endpoint, '/');
        $server = [
            'HTTP_ACCEPT'        => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . session('api_token', ''),
            'CONTENT_TYPE'       => 'application/json',
        ];

        $content = in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)
            ? json_encode($data)
            : null;

        $request = HttpRequest::create($uri, $method, $method === 'GET' ? $data : [], [], [], $server, $content);

        return app(
            \Illuminate\Contracts\Http\Kernel::class
        )->handle($request);
    }

    /**
     * Execute the HTTP call and always return an array.
     * Logs failures instead of crashing the page.
     */
    private function safeJson(callable $call, string $label): array
    {
        try {
            $response = $call();

            if (method_exists($response, 'json')) {
                $body = $response->json();
            } else {
                $body = json_decode($response->getContent(), true);
            }

            if (! is_array($body)) {
                $status = method_exists($response, 'status') ? $response->status() : $response->getStatusCode();
                $content = method_exists($response, 'body') ? $response->body() : $response->getContent();

                Log::warning("ApiClient: {$label} returned non-array response", [
                    'status' => $status,
                    'body'   => $content,
                    'token_present' => ! empty(session('api_token')),
                ]);
                return ['success' => false, 'data' => [], 'message' => 'API returned unexpected response.'];
            }

            return $body;

        } catch (\Exception $e) {
            Log::error("ApiClient: {$label} threw exception", [
                'error' => $e->getMessage(),
                'token_present' => ! empty(session('api_token')),
            ]);
            return ['success' => false, 'data' => [], 'message' => $e->getMessage()];
        }
    }

    // ----------------------------------------------------------------
    // Data helpers — array → stdClass so Blade ->prop access works
    // ----------------------------------------------------------------

    protected function toObject(mixed $data): mixed
    {
        if (is_null($data) || $data === []) {
            return null;
        }
        return json_decode(json_encode($data));
    }

    protected function toObjects(array $items): \Illuminate\Support\Collection
    {
        return collect($items)->map(fn ($item) => $this->toObject($item));
    }

    /**
     * Build a LengthAwarePaginator from an API response.
     * Items are converted to stdClass objects so Blade ->prop access works.
     */
    protected function makePaginator(
        array  $response,
        int    $defaultPerPage,
        string $path,
        array  $queryParams = []
    ): \Illuminate\Pagination\LengthAwarePaginator {
        $items = $this->toObjects($response['data'] ?? []);
        $meta  = $response['meta'] ?? [];

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $meta['total']        ?? $items->count(),
            $meta['per_page']     ?? $defaultPerPage,
            $meta['current_page'] ?? 1,
            ['path' => $path, 'query' => $queryParams]
        );
    }
}
