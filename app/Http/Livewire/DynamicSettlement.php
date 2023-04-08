<?php

namespace App\Http\Livewire;

use App\Models\User;
use App\Models\Agent;
use Livewire\Component;
use App\Models\Provider;

class DynamicSettlement extends Component
{
    public $user_type = 'Provider';
    public $user_id;
    public $user;
    public $found_status = true;
    public $email = NULL;
    public $suggestions = [];

    public function getDetails()
    {
        if (!strcasecmp($this->user_type, 'Provider')) {
            $this->suggestions = Provider::where('email', 'like', '%'.$this->email.'%')
                ->orWhere('first_name', 'like', '%'.$this->email.'%')
                ->orWhere('last_name', 'like', '%'.$this->email.'%')
                ->limit(5)
                ->get();
        } 
        // elseif ($this->user_type == 'User') {
        //     $this->user = User::where('email', 'like', '%'.$this->email.'%')
        //         ->first();
        // } 
        elseif ($this->user_type == 'Agent') {
            $this->suggestions = Agent::where('email', 'like', '%'.$this->email.'%')
                ->orWhere('name', 'like', '%'.$this->email.'%')
                ->limit(5)
                ->get();
        }

        // Update: If there are no suggestions and this function was called then the user_type was changed so it might be a good idea to reset the email and other user details that might have been previously assigned.
    }

    public function selectUser($user_id)
    {
        if(strcasecmp($this->user_type, 'Provider') == 0) {
            $this->user = Provider::where('id', $user_id)->first();
        }
        elseif(strcasecmp($this->user_type, 'Agent') == 0) {
            $this->user = Agent::where('id', $user_id)->first();
        }
        if (empty($this->user)) {
            $this->found_status = false;
        } else {
            $this->email = $this->user->email;
            $this->user_id = $user_id;
            $this->found_status = true;
        }
    }

    public function mount($type = 'Provider', $id = null)
    {
        $this->user_type = $type;
        $this->user_id   = $id;
    }

    public function render()
    {
        if(!empty($this->email)) {
            $this->getDetails();
        }

        return view('livewire.dynamic-settlement');
    }
}
