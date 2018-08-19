<?php

namespace Illuminate\Auth\Events;

class Attempting
{
    /**
     * The credentials for the user.
     *
     * @var array
     */
    public $credentials;

    /**
     * Indicates if the user should be "remembered".
     *
     * @var bool
     */
    public $remember;

    /**
     * The guard this attempt is made to.
     *
     * @var string
     */
    public $guard;

    /**
     * Create a new event instance.
     *
     * @param  array  $credentials
     * @param  bool  $remember
     * @param  string  $guard
     * @return void
     */
    public function __construct($credentials, $remember, $guard)
    {
        $this->remember = $remember;
        $this->credentials = $credentials;
        $this->guard = $guard;
    }
}
