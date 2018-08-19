<?php

namespace Illuminate\Auth\Events;

use Illuminate\Queue\SerializesModels;

class Authenticated
{
    use SerializesModels;

    /**
     * The authenticated user.
     *
     * @var \Illuminate\Contracts\Auth\Authenticatable
     */
    public $user;

    /**
     * The guard the user is authenticating to.
     *
     * @var string
     */
    public $guard;

    /**
     * Create a new event instance.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string  $guard
     * @return void
     */
    public function __construct($user, $guard)
    {
        $this->user = $user;
        $this->guard = $guard;
    }
}
