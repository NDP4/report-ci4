<?php

namespace App\Controllers;

class DashboardController extends BaseController
{
    public function __construct()
    {
        // Check if user is logged in
        if (!session()->get('logged_in')) {
            header('Location: ' . base_url('/login'));
            exit();
        }
    }

    public function index()
    {
        $data = [
            'title' => 'Dashboard',
            'user' => [
                'username' => session()->get('username'),
                'email' => session()->get('email'),
                'role' => session()->get('role')
            ]
        ];

        return view('dashboard/index', $data);
    }

    public function about()
    {
        $data = [
            'title' => 'About Me',
            'user' => [
                'username' => session()->get('username'),
                'email' => session()->get('email'),
                'role' => session()->get('role')
            ]
        ];

        return view('dashboard/about', $data);
    }
}
