<?php

namespace App\Services\RxNorm;

use App\Helpers\Helper;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

class RxNormService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('rxnorm.RXNORM_BASE_URL');
    }

    /**
     * Search for drugs by name.
     * @param string $name
     * @return array | Exception
     */
    public function searchDrugs(string $name): array | Exception
    {
        try {
            $response = Http::get("{$this->baseUrl}/drugs.json", [
                'name' => $name
            ]);

            if (!$response->successful() || !isset($response->json()['drugGroup']['conceptGroup'])) {
                throw new Exception('No drugs found', Response::HTTP_NOT_FOUND);
            }

            // Take the only concept group for SBD
            $sbdGroup = collect($response->json()['drugGroup']['conceptGroup'])->firstWhere('tty', 'SBD');
            return collect($sbdGroup['conceptProperties'] ?? [])->take(5)->all();
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get detailed information for a given RxCUI.
     * @param string $rxcui
     * @return array | Exception
     */
    public function getDrugDetails(string $rxcui): array | Exception
    {
        try {
            $cacheKey = "rxnorm_details_{$rxcui}";
            $results = Helper::cacheGet($cacheKey);
            if ($results) {
                return $results;
            }

            $response = Http::get("{$this->baseUrl}/rxcui/{$rxcui}/historystatus.json");
            if (!$response->successful() || !isset($response->json()['rxcuiStatusHistory'])) {
                Helper::cacheDelete($cacheKey);
                throw new Exception('No drug details found', Response::HTTP_NOT_FOUND);
            }

            $name = $response->json()['rxcuiStatusHistory']['attributes']['name'];
            $data = $response->json()['rxcuiStatusHistory']['definitionalFeatures'];
            // Extract ingredient base names
            $baseNames = collect($data['ingredientAndStrength'])
                ->pluck('baseName')
                ->unique()
                ->values()
                ->all();

            // Extract dose form group names
            $doseForms = collect($data['doseFormGroupConcept'])
                ->pluck('doseFormGroupName')
                ->unique()
                ->values()
                ->all();

            Helper::cachePut($cacheKey, [
                'rxcui' => $rxcui,
                'name' => $name,
                'baseNames' => $baseNames,
                'doseForms' => $doseForms,
            ], now()->addHours(24));
            return [
                'rxcui' => $rxcui,
                'name' => $name,
                'baseNames' => $baseNames,
                'doseForms' => $doseForms,
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Validate if an RxCUI is a valid and active drug concept.
     * @param string $rxcui
     * @return bool | Exception
     */
    public function validateRxcui(string $rxcui): bool | Exception
    {
        try {
            $response = Http::get("{$this->baseUrl}/rxcui/{$rxcui}/properties.json");
            if (!$response->successful() || !isset($response->json()['properties'])) {
                return false;
            }
            return $response->json()['properties']['rxcui'] == $rxcui;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
