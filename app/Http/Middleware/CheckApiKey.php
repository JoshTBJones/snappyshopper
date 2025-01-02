<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ApiKey;

class CheckApiKey
{
    /**
     * Validate the API key from the request's Bearer token and attach the organisation ID.
     * 
     * This middleware expects an API key in the Authorization header as a Bearer token.
     * It validates the key against the api_keys table and adds the associated 
     * organisation_id to the request for downstream use.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed|\Illuminate\Http\JsonResponse Returns 401 if API key is missing or invalid
     */
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->bearerToken();

        if (!$apiKey) {
            return response()->json(['message' => 'API key is missing'], 401);
        }

        $keyRecord = ApiKey::where('key', hash('sha256', $apiKey))->first();

        if (!$keyRecord) {
            return response()->json(['message' => 'Invalid API key'], 401);
        }

        // Add the organisation ID to the request
        $request->merge(['organisation_id' => $keyRecord->organisation_id]);

        return $next($request);
    }
} 