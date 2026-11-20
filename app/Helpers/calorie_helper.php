<?php
if (!function_exists('calculateCaloriesBurned')) {
    /**
     * Calculate calories burned based on activity type and duration
     */
    function calculateCaloriesBurned($activity_type, $duration_minutes, $weight_kg = 70)
    {
        // MET values (Metabolic Equivalent of Task) for different activities
        $met_values = [
            'walking' => 3.5,
            'running' => 8.0,
            'cycling' => 7.5,
            'swimming' => 6.0,
            'yoga' => 2.5,
            'weight_lifting' => 6.0,
            'cardio' => 7.0,
            'general_exercise' => 5.0
        ];
        
        $met = $met_values[$activity_type] ?? $met_values['general_exercise'];
        
        // Calories burned = MET * weight in kg * time in hours
        $calories_burned = $met * $weight_kg * ($duration_minutes / 60);
        
        return round($calories_burned);
    }
}

if (!function_exists('getActivitySuggestions')) {
    /**
     * Get activity suggestions based on client goals
     */
    function getActivitySuggestions($goal_id, $duration_minutes = 30)
    {
        $suggestions = [
            'weight_loss' => [
                ['name' => 'Running', 'calories' => 300, 'duration' => 30],
                ['name' => 'HIIT Workout', 'calories' => 350, 'duration' => 30],
                ['name' => 'Cycling', 'calories' => 280, 'duration' => 30]
            ],
            'muscle_gain' => [
                ['name' => 'Weight Training', 'calories' => 200, 'duration' => 45],
                ['name' => 'Circuit Training', 'calories' => 250, 'duration' => 40]
            ],
            'general_fitness' => [
                ['name' => 'Brisk Walking', 'calories' => 150, 'duration' => 30],
                ['name' => 'Swimming', 'calories' => 250, 'duration' => 30],
                ['name' => 'Yoga', 'calories' => 120, 'duration' => 45]
            ]
        ];
        
        return $suggestions[$goal_id] ?? $suggestions['general_fitness'];
    }
}