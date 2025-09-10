<?php

namespace App\Services;

use App\Helpers\Helper;
use App\Models\UserMedication;
use App\Services\RxNorm\RxNormService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;

class MedicationService
{
    protected RxNormService $rxNormService;

    public function __construct()
    {
        $this->rxNormService = new RxNormService();
    }

    /**
     * Get medications for a user
     * @param int $userId
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getMedications(int $userId, int $perPage = 10)
    {
        return UserMedication::where('user_id', $userId)->paginate($perPage);
    }

    /**
     * Add a medication for a user
     * @param int $userId
     * @param string $rxcui
     * @return \App\Models\UserMedication
     */
    public function addMedication(int $userId, string $rxcui)
    {
        return UserMedication::create([
            'user_id' => $userId,
            'rxcui' => $rxcui,
        ]);
    }

    /**
     * Get a medication for a user
     * @param int $userId
     * @param string $rxcui
     * @return \App\Models\UserMedication
     */
    public function getMedication(int $userId, string $rxcui)
    {
        return UserMedication::where('user_id', $userId)->where('rxcui', $rxcui)->first();
    }

    /**
     * Delete a medication for a user
     * @param int $userId
     * @param string $rxcui
     * @return \App\Models\UserMedication
     */
    public function deleteMedication(int $userId, string $rxcui)
    {
        return UserMedication::where('user_id', $userId)->where('rxcui', $rxcui)->delete();
    }

    /**
     * Get medication details
     * @param string $rxcui
     * @return array | Exception
     */
    public function getMedicationDetails(string $rxcui)
    {
        return $this->rxNormService->getDrugDetails($rxcui);
    }

    /**
     * Search drugs by name
     * @param string $name
     * @return array | Exception
     */
    public function searchDrugsByName(string $name)
    {
        try {
            $cacheKey = "rxnorm_search_{$name}";
            $results = Helper::cacheGet($cacheKey);
            if ($results) {
                return $results;
            }

            $searchResults = $this->rxNormService->searchDrugs($name);
            $results = [];
            foreach ($searchResults as $drug) {
                $details = $this->rxNormService->getDrugDetails($drug['rxcui']);
                if ($details) {
                    $results[] = [
                        'rxcui' => $details['rxcui'],
                        'name' => $details['name'],
                        'baseNames' => $details['baseNames'],
                        'doseForms' => $details['doseForms'],
                    ];
                }
            }
            Helper::cachePut($cacheKey, $results, now()->addHours(24));
            return $results;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get user medication list
     * @param int $userId
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator | Exception
     */
    public function userMedicationList(int $userId, int $perPage = 10)
    {
        try {
            $userMedications = $this->getMedications($userId, $perPage);
            $detailedList = $userMedications->getCollection()->map(function ($medication) {
                $details = $this->rxNormService->getDrugDetails($medication->rxcui);
                return [
                    'rxcui'      => $details['rxcui'],
                    'name'       => $details['name'],
                    'baseNames'  => $details['baseNames'] ?? [],
                    'doseForms'  => $details['doseForms'] ?? [],
                ];
            });
           return $userMedications->setCollection($detailedList);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Add a new medication for a user
     * @param int $userId
     * @param string $rxcui
     * @return \App\Models\UserMedication | Exception
     */
    public function addUserNewMedication(int $userId, string $rxcui)
    {
        try {
            $medication = $this->getMedication($userId, $rxcui);
            if ($medication) {
                throw new Exception('Medication already exists for this user.', Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            if (!$this->rxNormService->validateRxcui($rxcui)) {
                throw new Exception('Invalid or inactive RxCUI.', Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $medication = $this->addMedication($userId, $rxcui);
            $medication->makeHidden(['updated_at', 'id', 'user_id']);
            return $medication;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Delete a medication for a user
     * @param int $userId
     * @param string $rxcui
     * @return void | Exception
     */
    public function deleteUserMedication(int $userId, string $rxcui)
    {
        try {
            if (!$this->rxNormService->validateRxcui($rxcui)) {
                throw new Exception('Invalid or inactive RxCUI.', Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $medication = UserMedication::where('user_id', $userId)
                ->where('rxcui', $rxcui)
                ->firstOrFail();
            $medication->delete();
        } catch (ModelNotFoundException $e) {
            throw new Exception('Medication not found for this user or already deleted.', Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            throw $e;
        }
    }
}
