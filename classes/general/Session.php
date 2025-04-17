<?php

class Session
{
    private $session;

    public function __construct()
    {
        session_start();

        $this->session = $_SESSION ?? [];
    }

    public function get($key)
    {
        return $this->session[$key] ?? null;
    }

    public function set()
    {
        $this->session = [];
    }

    public function put($key, $value)
    {
        $this->session[$key] = $value;
    }

    public function save()
    {
        $_SESSION = $this->session;
    }

    public function forget($key)
    {
        unset($this->session[$key]);
    }
}