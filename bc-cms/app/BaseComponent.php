<?php

namespace App;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class BaseComponent extends Component
{
    public function sendSuccess($message)
    {
        session()->flash('success', $message);
        return;
    }

    public function sendError($message)
    {
        if ($message instanceof MessageBag and count($message->getMessages())) {
            $html = [];
            foreach ($message->getMessages() as $v) {
                $html = array_merge($html, $v);
            }
            $message = implode('<br/>', $html);
        }
        session()->flash('danger', $message);
        return;
    }

    public function hasPermission($permission)
    {
        return Auth::user()->hasPermission($permission);
    }

    public function checkPermission($permission)
    {
        if(!$this->hasPermission($permission)){
            abort(403, 'Unauthorized action.');
        }
    }

    public function paginationView()
    {
        return 'livewire.pagination';
    }
}

