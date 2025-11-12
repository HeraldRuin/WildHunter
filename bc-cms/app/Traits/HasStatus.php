<?php


namespace App\Traits;


trait HasStatus
{
    public function getStatusBadgeAttribute(){
        switch ($this->status){
            case "publish": return "success";
            case "completed": return "success";
            case "draft":  return "secondary";
            case "pending":  return "secondary";
            case "processing":  return "warning";
        }
    }
    public function getStatusTextAttribute(){
        return status_to_text($this->status);
    }

    public function scopeIsPublic($query){
        return $query->where('status','publish');
    }
}
