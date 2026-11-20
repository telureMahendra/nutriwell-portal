<?php
// App\Controllers\Apis
namespace App\Controllers\Apis;

use App\Models\HealthTrackerModel;
use CodeIgniter\RESTful\ResourceController;

class HealthTrackerController extends ResourceController
{
    protected $format = 'json';
    protected $healthTracker;

    public function __construct()
    {
        $this->healthTracker = new HealthTrackerModel();
    }

    public function saveHealthData()
    {
        $input = $this->request->getJSON(true);

        if (empty($input['date'])) {
            return $this->respond(['status' => false, 'message' => 'Date is required'], 400);
        }

        $clientId = 1; // Replace with actual client/session ID
        $date = date('Y-m-d', strtotime($input['date']));
        $now = date('Y-m-d H:i:s');

        try {
            // === 1️⃣ WATER ===
            if (!empty($input['water']['glass'])) {
                $waterData = [
                    'client_id' => $clientId,
                    'date' => $date,
                    'quantity' => $input['water']['glass'],
                    'created_on' => $now,
                    'created_by' => $clientId,
                    'updated_on' => $now,
                    'updated_by' => $clientId
                ];
                $this->healthTracker->upsertWater($clientId, $date, $waterData);
            }

            // === 2️⃣ CALORIES ===
            if (!empty($input['calories']['foodData'])) {
                foreach ($input['calories']['foodData'] as $foodType) {
                    $foodTypeId = $foodType['foodTypeId'];
                    foreach ($foodType['foodList'] as $food) {
                        $foodData = [
                            'client_id' => $clientId,
                            'food_id' => $food['foodId'],
                            'food_type_id' => $foodTypeId,
                            'quantity' => $food['foodQty'],
                            'date' => $date,
                            'created_on' => $now,
                            'created_by' => $clientId,
                            'updated_on' => $now,
                            'updated_by' => $clientId
                        ];
                        $this->healthTracker->upsertCalorie($clientId, $date, $foodData);
                    }
                }
            }

            // === 3️⃣ STEPS ===
            // if (!empty($input['steps'])) {
            //     $this->healthTracker->upsertSteps($clientId, $date, [
            //         'client_id' => $clientId,
            //         'date' => $date,
            //         'steps' => $input['steps'],
            //         'cal_burned' => $input['calBurned'] ?? 0,
            //         'created_on' => $now,
            //         'created_by' => $clientId,
            //         'updated_on' => $now,
            //         'updated_by' => $clientId
            //     ]);
            // }

            // === 4️⃣ SLEEP ===
            if (!empty($input['sleep'])) {
                $sleep = $input['sleep'];
                $this->healthTracker->upsertSleep($clientId, $date, [
                    'client_id' => $clientId,
                    'date' => $date,
                    'hours' => $sleep['hours'] ?? 0,
                    'minutes' => $sleep['minutes'] ?? 0,
                    'start_time' => $sleep['startTime'] ?? null,
                    'end_time' => $sleep['endTime'] ?? null,
                    'created_on' => $now,
                    'created_by' => $clientId,
                    'updated_on' => $now,
                    'updated_by' => $clientId
                ]);
            }

            // === 5️⃣ WORKOUT ===
            if (!empty($input['workout'])) {
                $workout = $input['workout'];
                $this->healthTracker->upsertWorkout($clientId, $date, [
                    'client_id' => $clientId,
                    'date' => $date,
                    'hours' => $workout['hours'] ?? 0,
                    'minutes' => $workout['minutes'] ?? 0,
                    'start_time' => $workout['startTime'] ?? null,
                    'end_time' => $workout['endTime'] ?? null,
                    'created_on' => $now,
                    'created_by' => $clientId,
                    'updated_on' => $now,
                    'updated_by' => $clientId
                ]);
            }

            // === 6️⃣ WEIGHT / BMI ===
            if (!empty($input['bmi'])) {
                $bmi = $input['bmi'];
                $this->healthTracker->upsertBMI($clientId, $date, [
                    'client_id' => $clientId,
                    'date' => $date,
                    'age' => $bmi['age'] ?? null,
                    'height' => $bmi['height'] ?? null,
                    'height_unit' => $bmi['heightUnit'] ?? 'CM',
                    'weight' => $bmi['weight'] ?? null,
                    'created_on' => $now,
                    'created_by' => $clientId,
                    'updated_on' => $now,
                    'updated_by' => $clientId
                ]);
            }

            return $this->respond(['status' => true, 'message' => 'Health data saved successfully'], 200);

        } catch (\Throwable $th) {
            return $this->respond([
                'status' => false,
                'message' => 'Error: ' . $th->getMessage()
            ], 500);
        }
    }
}
