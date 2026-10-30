<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DeepLTranslator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class TranslateController extends Controller
{
    public function translate(Request $request, DeepLTranslator $translator): JsonResponse
    {
        try {
            $user = $request->user();
            $rateKey = 'admin-translate:'.($user ? $user->id : $request->ip());

            // 150 requests per minute rate limit per admin
            if (! RateLimiter::attempt($rateKey, 150, fn () => true, 60)) {
                return response()->json([
                    'translated' => null,
                    'translations' => null,
                    'reason' => 'rate_limited',
                    'message' => 'Too many translations, try again in a moment',
                ], 200);
            }

            $fields = $request->input('fields');
            if (! is_array($fields) || empty($fields)) {
                $fields = $request->except(['_token', 'autoOn']);
            }

            if (empty($fields)) {
                return response()->json([
                    'translated' => [],
                    'translations' => [],
                    'reason' => null,
                    'message' => null,
                ], 200);
            }

            $result = $translator->translate($fields);

            if ($result === null) {
                return response()->json([
                    'translated' => null,
                    'translations' => null,
                    'reason' => 'unavailable',
                    'message' => 'Auto translation unavailable. You can type the French yourself.',
                ], 200);
            }

            return response()->json([
                'translated' => $result,
                'translations' => $result,
                'reason' => null,
                'message' => null,
            ], 200);
        } catch (\Throwable $e) {
            Log::warning('TranslateController error: '.$e->getMessage());

            return response()->json([
                'translated' => null,
                'translations' => null,
                'reason' => 'unavailable',
                'message' => 'Auto translation unavailable. You can type the French yourself.',
            ], 200);
        }
    }
}
