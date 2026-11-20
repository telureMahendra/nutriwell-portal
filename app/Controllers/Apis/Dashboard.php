<?php

namespace App\Controllers\Apis;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

class Dashboard extends ResourceController
{
    use ResponseTrait;

    protected $client_login_model;
    protected $water_model;
    protected $calorie_model;
    protected $bmi_model;
    protected $sleep_model;
    protected $workout_model;
    protected $steps_model;
    protected $calories_burned_model;
    protected $weight_model;
    protected $calories_target_model;
    protected $steps_target_model;
    protected $user_reward_model;
    protected $notification_model;
    protected $db;

    public function __construct()
    {
        $this->db                    = \Config\Database::connect();
        $this->client_login_model    = model('App\Models\Client_login_model');
        $this->water_model           = model('App\Models\Water_model');
        $this->calorie_model         = model('App\Models\Calorie_model');
        $this->bmi_model             = model('App\Models\Bmi_model');
        $this->sleep_model           = model('App\Models\Sleep_model');
        $this->workout_model         = model('App\Models\Workout_model');
        $this->steps_model           = model('App\Models\Steps_model');
        $this->calories_burned_model = model('App\Models\Calories_burned_model');
        $this->weight_model          = model('App\Models\Weight_model');
        $this->calories_target_model = model('App\Models\CaloriesTargetModel');
        $this->steps_target_model    = model('App\Models\StepsTargetModel');
        $this->user_reward_model     = model('App\Models\UserRewardModel');
        $this->notification_model    = model('App\Models\Notification_model');
    }

    /**
     * Count consecutive days where the user logged water, food AND sleep.
     * Today is skipped if not yet complete (day is still in progress).
     * Returns 0 if no complete days found.
     */
    private function calculateDayStreak($client_id)
    {
        $streak = 0;
        for ($i = 0; $i <= 365; $i++) {
            $checkDate = date('Y-m-d', strtotime("-{$i} days"));

            $hasWater   = $this->water_model->where('client_id', $client_id)->where('date', $checkDate)->countAllResults() > 0;
            $hasCalorie = $this->calorie_model->where('client_id', $client_id)->where('date', $checkDate)->countAllResults() > 0;
            $hasSleep   = $this->sleep_model->where('client_id', $client_id)->where('date', $checkDate)->countAllResults() > 0;

            if ($hasWater && $hasCalorie && $hasSleep) {
                $streak++;
            } elseif ($i === 0) {
                // Today is incomplete — skip it, don't break the streak yet
                continue;
            } else {
                break;
            }
        }
        return $streak;
    }

    private function headers()
    {
        $this->response->setHeader('Access-Control-Allow-Origin', '*');
        $this->response->setHeader('Access-Control-Allow-Methods', 'POST, OPTIONS');
        $this->response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        $this->response->setHeader('Content-Type', 'application/json');
    }

    /**
     * Save or update daily summary (Upsert)
     */
    public function save_daily_summary()
    {
        $this->headers();
        $data = $this->request->getJSON(true);
        $token = $data['tocken'] ?? '';
        $date = $data['date'] ?? date('Y-m-d');

        // 🔒 Validate token
        $user = $this->client_login_model->checkTocken($token);
        // if (empty($user)) {
        //     return $this->respond(['status' => false, 'msg' => 'Invalid token or user not found'], 401);
        // }
        $client_id = $user[0]->client_id;
        $now = date('Y-m-d H:i:s');

        /** 1️⃣ Water Tracker */
        if (!empty($data['water'])) {
            $record = $this->water_model->where(['client_id' => $client_id, 'date' => $date])->first();
            $payload = [
                'client_id' => $client_id,
                'date' => $date,
                'quantity' => $data['water']['glass'] ?? 0,
                'updated_on' => $now,
                'updated_by' => $client_id
            ];

            if ($record) {
                $this->water_model->update($record['water_id'], $payload);
            } else {
                $payload['created_on'] = $now;
                $payload['created_by'] = $client_id;
                $this->water_model->insert($payload);
            }
        }

        /** 2️⃣ Calories (Food Intake) */
        if (!empty($data['calories']['foodData'])) {
            $this->calorie_model->where(['client_id' => $client_id, 'date' => $date])->delete();
            foreach ($data['calories']['foodData'] as $foodType) {
                foreach ($foodType['foodList'] as $food) {
                    $calorie = $this->calorie_model->getFoodCal($food['foodId']);
                    if (!$calorie)
                        continue;

                    $this->calorie_model->insert([
                        'client_id' => $client_id,
                        'food_id' => $food['foodId'],
                        'food_type_id' => $foodType['foodTypeId'],
                        'quantity' => $food['foodQty'] ?? 1,
                        'calories' => $calorie[0]->calories * ($food['foodQty'] ?? 1),
                        'date' => $date,
                        'created_on' => $now,
                        'created_by' => $client_id,
                        'updated_on' => $now,
                        'updated_by' => $client_id
                    ]);
                }
            }
        }

        /** 3️⃣ Calories Burned */
        //     if (!empty($data['calories_burned'])) {
        //         $caloriesValue = $data['calories_burned'][0]['calories_burned'] ?? 0;

        //         $this->db->query("
        //     INSERT INTO calories_burned (client_id, date, calories_burned)
        //     VALUES (?, ?, ?)
        //     ON DUPLICATE KEY UPDATE
        //         calories_burned = VALUES(calories_burned),
        //         updated_at = CURRENT_TIMESTAMP
        // ", [$client_id, $date, $caloriesValue]);
        //     }




        if (!empty($data['calories_burned'])) {

            $existing = $this->calories_burned_model
                ->where(['client_id' => $client_id, 'date' => $date])
                ->first();

            $payload = [
                'client_id' => $client_id,
                'date' => $date,
                'calories_burned' => $data['calories_burned'] ?? 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),

            ];

            if ($existing && isset($existing['id'])) {
                // update if exists
                $this->calories_burned_model->update($existing['id'], $payload);
            } else {
                // insert if new
                $this->calories_burned_model->insert($payload);
            }


        }

        /** 4️⃣ BMI */
        if (!empty($data['bmi'])) {
            $record = $this->bmi_model->where(['client_id' => $client_id, 'date' => $date])->first();
            $height = $data['bmi']['height'] ?? 0;
            $weight = $data['bmi']['weight'] ?? 0;
            $bmiValue = ($height > 0) ? $weight / pow(($height / 100), 2) : 0;

            $payload = [
                'client_id' => $client_id,
                'date' => $date,
                'age' => $data['bmi']['age'] ?? 0,
                'height' => $height,
                'weight' => $weight,
                'bmi_value' => round($bmiValue, 2),
                'updated_on' => $now,
                'updated_by' => $client_id
            ];

            if ($record) {
                $bmiId = $record['bmi_id'] ?? $record['bmi_record_id'] ?? null;
                $this->bmi_model->update($bmiId, $payload);
            } else {
                $payload['created_on'] = $now;
                $payload['created_by'] = $client_id;
                $this->bmi_model->insert($payload);
            }
        }

        /** 5️⃣ Sleep Tracker */
        if (!empty($data['sleep'])) {
            $record = $this->sleep_model->where(['client_id' => $client_id, 'date' => $date])->first();
            $payload = [
                'client_id' => $client_id,
                'date' => $date,
                'hours' => $data['sleep']['hours'] ?? 0,
                'minutes' => $data['sleep']['minutes'] ?? 0,
                'start_time' => $data['sleep']['start_time'] ?? null,
                'end_time' => $data['sleep']['end_time'] ?? null,
                'updated_on' => $now,
                'updated_by' => $client_id
            ];

            if ($record) {
                $this->sleep_model->update($record['sleep_id'], $payload);
            } else {
                $payload['created_on'] = $now;
                $payload['created_by'] = $client_id;
                $this->sleep_model->insert($payload);
            }
        }

        /** 6️⃣ Workout Tracker */
        if (!empty($data['workout'])) {
            $record = $this->workout_model->where(['client_id' => $client_id, 'date' => $date])->first();
            $payload = [
                'client_id' => $client_id,
                'date' => $date,
                'workout_name' => $data['workout']['workout_name'] ?? null,
                'hours' => $data['workout']['hours'] ?? 0,
                'minutes' => $data['workout']['minutes'] ?? 0,
                'start_time' => $data['workout']['start_time'] ?? null,
                'end_time' => $data['workout']['end_time'] ?? null,
                'updated_on' => $now,
                'updated_by' => $client_id
            ];

            if ($record) {
                $this->workout_model->update($record['workout_id'], $payload);
            } else {
                $payload['created_on'] = $now;
                $payload['created_by'] = $client_id;
                $this->workout_model->insert($payload);
            }
        }

        /** 7️⃣ Steps Tracker */
        if (!empty($data['steps'])) {
            $record = $this->steps_model->where(['client_id' => $client_id, 'date' => $date])->first();
            $payload = [
                'client_id' => $client_id,
                'date' => $date,
                'steps' => $data['steps']['count'] ?? 0,
                'updated_at' => $now,
            ];

            if ($record) {
                $this->steps_model->update($record['id'], $payload);
            } else {
                $payload['created_at'] = $now;
                $this->steps_model->insert($payload);
            }
        }

        /** 8️⃣ Weight Tracker */
        if (!empty($data['weight'])) {
            $record = $this->weight_model->where(['client_id' => $client_id, 'created_on' => $date])->first();
            $payload = [
                'client_id' => $client_id,
                'created_on' => $date,
                'weight' => $data['weight']['weight'] ?? 0,
                'weight_unit' => $data['weight']['weight_unit'] ?? 'KG',
                'updated_on' => $now,
                'updated_by' => $client_id
            ];

            if ($record) {
                $this->weight_model->update($record['weight_id'], $payload);
            } else {
                $payload['created_on'] = $now;
                $payload['created_by'] = $client_id;
                $this->weight_model->insert($payload);
            }
        }

        /** 🧾 Response: Daily Summary */
        $summary = [
            'water' => $this->water_model->getWaterIntake($client_id, $date),
            // 'calorie' => $this->calorie_model->getCalorieSummary($client_id, $date),
            'calorie' => [
                'summary' => $this->calorie_model->getCalorieSummary($client_id, $date),
                'foods' => $this->calorie_model->getFoodByDate($client_id, $date),
            ],
            'bmi' => $this->bmi_model->getBmiByDate($client_id, $date),
            'sleep' => $this->sleep_model->getSleepByDate($client_id, $date),
            'workout' => $this->workout_model->getWorkoutByDate($client_id, $date),
            'steps' => $this->steps_model->getStepsByDate($client_id, $date),
            'calories_burned' => $this->calories_burned_model
                ->where('client_id', $client_id)
                ->where('date', $date)
                ->findAll(),
            // 'weight' => $this->weight_model
            //     ->where('client_id', $client_id)
            //     ->where('created_on', $date)
            //     ->first(),

            'weight' => $this->weight_model
                ->where('client_id', $client_id)
                ->where('DATE(created_on)', $date)
                ->orderBy('created_on', 'DESC')
                ->first(),
        ];

        return $this->respond([
            'status' => true,
            'msg' => 'Daily summary updated successfully',
            'data' => $summary
        ]);
    }

    /**
     * Get data for a date range
     */
    public function get_daily_summary()
    {


        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));

        $json_data = json_decode($this->_request) ?? (object)[];

        $start_date = $json_data->start_date ?? date('Y-m-d', strtotime('-30 days'));
        $end_date = $json_data->end_date ?? date('Y-m-d');
        $tocken = $json_data->tocken ?? '';


        $user = $this->client_login_model->checkTocken($tocken);
        if (empty($user)) {
            return $this->respond(['status' => false, 'msg' => 'Invalid token or user not found'], 401);
        }
        $client_id = $user[0]->client_id;

        // 📊 Aggregate data by date
        $response = [
            'water' => $this->water_model
                ->select('date, SUM(quantity) as total_glass')
                ->where('client_id', $client_id)
                ->where('date >=', $start_date)
                ->where('date <=', $end_date)
                ->groupBy('date')->orderBy('date', 'ASC')->findAll(),

            // 'calories' => $this->calorie_model
            //     // ->select('date, SUM(quantity) as total_calories')
            //     ->select('date, SUM(calories) as total_calories')
            //     ->where('client_id', $client_id)
            //     ->where('date >=', $start_date)
            //     ->where('date <=', $end_date)
            //     ->groupBy('date')->orderBy('date', 'ASC')->findAll(),

            'calories' => [
                'summary' => $this->calorie_model
                    ->select('calorie.date, SUM(COALESCE(calorie.calories, food.calories * calorie.quantity)) as total_calories')
                    ->join('food', 'food.food_id = calorie.food_id', 'left')
                    ->where('calorie.client_id', $client_id)
                    ->where('calorie.date >=', $start_date)
                    ->where('calorie.date <=', $end_date)
                    ->groupBy('calorie.date')
                    ->orderBy('calorie.date', 'ASC')
                    ->findAll(),

                'foods' => $this->calorie_model
                    ->getFoodByDateRange($client_id, $start_date, $end_date),
            ],

            'bmi' => $this->bmi_model
                ->where('client_id', $client_id)
                ->where('date >=', $start_date)
                ->where('date <=', $end_date)
                ->orderBy('date', 'ASC')->findAll(),

            'sleep' => $this->sleep_model
                ->where('client_id', $client_id)
                ->where('date >=', $start_date)
                ->where('date <=', $end_date)
                ->orderBy('date', 'ASC')->findAll(),

            'workout' => $this->workout_model
                ->where('client_id', $client_id)
                ->where('date >=', $start_date)
                ->where('date <=', $end_date)
                ->orderBy('date', 'ASC')->findAll(),

            'steps' => $this->steps_model
                ->select('date, SUM(steps) as total_steps')
                ->where('client_id', $client_id)
                ->where('date >=', $start_date)
                ->where('date <=', $end_date)
                ->groupBy('date')->orderBy('date', 'ASC')->findAll(),

            'calories_burned' => $this->calories_burned_model
                ->select('date, SUM(calories_burned) as total_burned')
                ->where('client_id', $client_id)
                ->where('date >=', $start_date)
                ->where('date <=', $end_date)
                ->groupBy('date')->orderBy('date', 'ASC')->findAll(),

            // 'weight' => $this->weight_model
            //     ->select('created_on, weight')
            //     ->where('client_id', $client_id)
            //     ->where('created_on >=', $start_date)
            //     ->where('created_on <=', $end_date)
            //     ->orderBy('created_on', 'ASC')->findAll(),

            // 'weight' => $this->weight_model
            //     ->select('DATE(created_on) as date, MAX(weight) as weight')
            //     ->where('client_id', $client_id)
            //     ->where('created_on >=', $start_date)
            //     ->where('created_on <=', $end_date)
            //     ->groupBy('DATE(created_on)')
            //     ->orderBy('date', 'ASC')
            //     ->findAll(),

            'weight' => $this->weight_model
                ->select('DATE(created_on) as date, weight')
                ->where('client_id', $client_id)
                ->where('created_on >=', $start_date)
                ->where('created_on <=', $end_date)
                ->orderBy('created_on', 'DESC')
                ->findAll(),



        ];

        // ── Targets ──────────────────────────────────────────────────────────
        $profileTargets = $this->db->table('client_profile')
            ->select('target_water, target_sleep, target_weight')
            ->where('client_id', $client_id)
            ->get()->getRow();

        $response['targets'] = [
            'water'   => $profileTargets ? (int)$profileTargets->target_water               : 8,    // glasses/day
            'calorie' => $this->calories_target_model->getCurrentTarget($client_id, null, 'intake'),
            'steps'   => $this->steps_target_model->getCurrentTarget($client_id),
            'weight'  => $profileTargets ? (float)($profileTargets->target_weight ?? 0)     : 0.0,  // kg
            'sleep'   => $profileTargets ? (int)$profileTargets->target_sleep               : 480,  // minutes/day
        ];

        // ── Notification count (total sent notifications) ─────────────────
        $response['notification_count'] = (int)$this->db->table('notifications')
            ->where('sent_at IS NOT NULL', null, false)
            ->countAllResults();

        // ── Days streak (continuous water + food + sleep logging) ─────────
        $response['days_streak'] = $this->calculateDayStreak($client_id);

        // ── Daily score (reward points earned today + cumulative total) ───
        $today = date('Y-m-d');
        $dailyPointsRow = $this->db->table('user_rewards')
            ->select('COALESCE(SUM(points_value), 0) AS points')
            ->where('user_id', $client_id)
            ->where("DATE(created_at)", $today)
            ->get()->getRow();
        $response['daily_score'] = [
            'today_points' => $dailyPointsRow ? (int)$dailyPointsRow->points : 0,
            'total_points' => $this->user_reward_model->getUserTotalPoints($client_id),
        ];

        return $this->respond([
            'status' => true,
            'msg' => 'Data fetched successfully',
            'data' => $response
        ]);
    }
}
