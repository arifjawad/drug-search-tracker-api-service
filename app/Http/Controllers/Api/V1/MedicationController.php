<?php


namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\MedicationAddRequest;
use App\Http\Requests\MedicationListRequest;
use App\Http\Requests\MedicationSearchRequest;
use App\Services\ResponseService;
use App\Services\MedicationService;
use Exception;
use Illuminate\Http\Response;

class MedicationController extends Controller
{
    protected MedicationService $medicationService;

    public function __construct()
    {
        $this->medicationService = new MedicationService();
    }

    public function search(MedicationSearchRequest $request)
    {
        try {
            $results = $this->medicationService->searchDrugsByName($request->validated('drug_name'));
            return ResponseService::apiResponse(Response::HTTP_OK, 'Search results', $results);
        } catch (Exception $e) {
            return ResponseService::apiResponse($e->getCode() ?? Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage() ?? 'Something went wrong');
        }
    }

    public function index(MedicationListRequest $request)
    {
        try {
            $paginatedResult = $this->medicationService->userMedicationList(auth()->user()->id, $request->validated('per_page') ?? 10);
            return ResponseService::apiResponse(Response::HTTP_OK, 'User medications', $paginatedResult);
        } catch (Exception $e) {
            return ResponseService::apiResponse(
                $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR,
                $e->getMessage() ?: 'Something went wrong'
            );
        }
    }


    public function add(MedicationAddRequest $request)
    {
        try {
            $medication = $this->medicationService->addUserNewMedication(auth()->user()->id, $request->validated('rxcui'));
            return ResponseService::apiResponse(Response::HTTP_CREATED, 'Drug added to your list', $medication);
        } catch (Exception $e) {
            return ResponseService::apiResponse(
                $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR,
                $e->getMessage() ?: 'Something went wrong'
            );
        }
    }

    public function delete(string $rxcui)
    {
        try {
            $this->medicationService->deleteUserMedication(auth()->user()->id, $rxcui);
            return ResponseService::apiResponse(Response::HTTP_NO_CONTENT, 'Drug deleted from your list');
        } catch (Exception $e) {
            return ResponseService::apiResponse(
                $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR,
                $e->getMessage() ?: 'Something went wrong'
            );
        }
    }
}
