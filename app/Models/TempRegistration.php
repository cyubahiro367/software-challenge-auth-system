<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TempRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'unique_identifier',
        'current_step',
        'step_data',
        'step_1_completed',
        'step_2_completed',
        'step_3_completed',
        'step_4_completed',
        'step_5_completed',
    ];

    protected $casts = [
        'step_data' => 'array',
        'step_1_completed' => 'boolean',
        'step_2_completed' => 'boolean',
        'step_3_completed' => 'boolean',
        'step_4_completed' => 'boolean',
        'step_5_completed' => 'boolean',
    ];

    protected $attributes = [
        'current_step' => 1,
    ];

    // Boot method to set defaults
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tempRegistration) {
            if (empty($tempRegistration->unique_identifier)) {
                $tempRegistration->unique_identifier = Str::uuid();
            }
        });
    }

    // Find by unique identifier
    public static function findByIdentifier($identifier)
    {
        return self::where('unique_identifier', $identifier)
            ->first();
    }

    // Update step data
    public function updateStepData($step, $data)
    {
        $stepData = $this->step_data ?? [];
        $stepData["step_{$step}"] = $data;

        $this->update([
            'step_data' => $stepData,
            "step_{$step}_completed" => true,
            'current_step' => min($step + 1, 5), // Don't exceed step 5
        ]);
    }

    // Get data for specific step
    public function getStepData($step)
    {
        return $this->step_data["step_{$step}"] ?? [];
    }

    // Check if step is completed
    public function isStepCompleted($step)
    {
        return $this->{"step_{$step}_completed"} ?? false;
    }

    // Check if user can access specific step
    public function canAccessStep($step)
    {
        if ($step == 1) {
            return true;
        }

        // User can access a step if the previous step is completed
        return $this->isStepCompleted($step - 1);
    }

    // Get all collected data for final review
    public function getAllData()
    {
        $allData = [];
        for ($i = 1; $i <= 5; $i++) {
            if ($this->isStepCompleted($i)) {
                $allData["step_{$i}"] = $this->getStepData($i);
            }
        }

        return $allData;
    }

    // Check if registration is complete
    public function isComplete()
    {
        return $this->step_1_completed &&
            $this->step_2_completed &&
            $this->step_3_completed &&
            $this->step_4_completed &&
            $this->step_5_completed;
    }
}
