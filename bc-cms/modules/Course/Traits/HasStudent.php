<?php

namespace Modules\Course\Traits;

use Modules\Course\Models\Course2User;

trait HasStudent
{
    public function addStudentById($user_id, $data = [])
    {
        return app(Course2User::class)->upsert([
            'course_id' => $this->id,
            'user_id' => $user_id,

            // Default status is active
            // allow user start course immediately
            'status' => 1,
            ...$data,
        ],['course_id','user_id']);
    }
}