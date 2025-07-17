<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KelolaKPI extends Model
{
    // ...existing code...
    
    /**
     * Calculate weighted score for this KPI
     */
    public function getWeightedScoreAttribute()
    {
        return round(($this->progress * $this->bobot) / 100, 2);
    }
    
    /**
     * Calculate achievement rate
     */
    public function getAchievementRateAttribute()
    {
        $target = $this->realisasi ?? 100;
        return $target > 0 ? round(($this->progress / $target) * 100, 2) : 0;
    }
    
    /**
     * Calculate performance score with timeline factor
     */
    public function getPerformanceScoreAttribute()
    {
        $timelineFactor = $this->timeline_realisasi ?? 100;
        return round(($this->progress * $timelineFactor) / 100, 2);
    }
    
    /**
     * Calculate sub-activities aggregated score
     */
    public function getSubActivitiesScoreAttribute()
    {
        $subActivities = $this->subActivities;
        
        if ($subActivities->isEmpty()) {
            return $this->progress;
        }
        
        $totalWeightedScore = 0;
        $totalBobot = 0;
        
        foreach ($subActivities as $sub) {
            $totalWeightedScore += ($sub->progress_percentage * $sub->bobot);
            $totalBobot += $sub->bobot;
        }
        
        return $totalBobot > 0 ? round($totalWeightedScore / $totalBobot, 2) : 0;
    }
    
    /**
     * Get performance level based on score
     */
    public function getPerformanceLevelAttribute()
    {
        $score = $this->performance_score;
        
        return match(true) {
            $score >= 90 => 'Excellent',
            $score >= 80 => 'Good',
            $score >= 70 => 'Average',
            $score >= 60 => 'Poor',
            default => 'Critical'
        };
    }
    
    /**
     * Get performance color for UI
     */
    public function getPerformanceColorAttribute()
    {
        $score = $this->performance_score;
        
        return match(true) {
            $score >= 90 => 'success',
            $score >= 80 => 'info',
            $score >= 70 => 'warning',
            $score >= 60 => 'danger',
            default => 'gray'
        };
    }
}
