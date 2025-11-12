<?php

namespace Modules\Course\Traits;    

use Modules\Course\Models\CourseModuleCompletion;
use Carbon\Carbon;


trait HasStudyActions
{

    protected function checkUserCanStudy($moduleId = null)
    {
        $user = auth()->user();
        if (!$this->course || !$user) {
            throw new \Exception('User or course not found');
        }
        
        $courseUser = $this->course->hasUser($user);

        if (!$courseUser || !$courseUser->status) {
            throw new \Exception('User is not allowed to study this course');
        }

        if ($moduleId) {
            $module = $this->course->modules()->whereId($moduleId)->first();
            if (!$module) {
                throw new \Exception('Module not found');
            }
            $this->module = $module;
        }
    }


    /**
     * Add a log to the course study log table, aim for time spent and percent only
     * 
     * NOTE: This used for normal module, not for quiz module
     * For Quiz module, please should have the `addQuizLog` method that calculate the score and time spent
     */
    protected function addLog()
    {
        $user = auth()->user();
        $moduleId = $this->moduleId;

        $log = app()->make(CourseModuleCompletion::class)->firstOrCreate([
            'course_id' => $this->course->id,
            'module_id' => $moduleId,
            'user_id' => $user->id
        ]);
        

        // if the module is already completed, return the log
        if($log->percent === 100 || $log->state === 1){
            return $log;
        }

        $maxDurationInSeconds = $module->duration * 60;
        
        $currentTimestamp = Carbon::now();

        // time_spent is the difference between the current timestamp and the last timestamp
        $lastTimestamp = Carbon::parse($log->last_studied_at); //  can be null
        $maxDeltaTime = 20; // Maximum time between two logs
        $defaultTimeSpent = 10; // Default time spent if the last timestamp is null
        $timeSpent = min($lastTimestamp ? $currentTimestamp->diffInSeconds($lastTimestamp, true) : $defaultTimeSpent, $maxDeltaTime);

        // if the time spent is greater than the maximum duration, set the time spent to the maximum duration
        $log->time_spent = min($maxDurationInSeconds, $log->time_spent + $timeSpent);
        $log->last_studied_at = $currentTimestamp;

        // update percent
        $log->percent = min(100, ($log->time_spent / $maxDurationInSeconds) * 100);

        // if the module is 100% completed, set the state to 1
        // TODO: add completion logic, eg: if the module is X% completed (not always 100%), set the state to 1
        if($log->percent === 100){
            $log->state = 1;
        }
        
        $log->save();


        // TODO: Update overall completion percent of the course for current user if module is completed

        return $log;
    }

    

    protected function getLog($module, $createIfNotExists = false)
    {
        $moduleCompletionClass = app()->make(CourseModuleCompletion::class);

        $moduleCompletion = $moduleCompletionClass::where([
            'course_id' => $this->course->id,
            'module_id' => $module->id,
            'user_id' => auth()->id()
        ])->first();

        if(!$moduleCompletion && $createIfNotExists){
            $moduleCompletion = new $moduleCompletionClass();
            $moduleCompletion->course_id = $this->course->id;
            $moduleCompletion->module_id = $module->id;
            $moduleCompletion->user_id = auth()->id();

            // add last studied at
            $moduleCompletion->last_studied_at = Carbon::now();
            $moduleCompletion->save();
        }

        return $moduleCompletion;
    }
}
