<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Check if user is logged in
        if (!session()->has('user_id')) {
            return redirect()->to('/login')->with('error', 'Please login first');
        }

        // Check if user has admin role
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Access denied. Admin role required.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
