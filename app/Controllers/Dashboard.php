<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    public function index()
    {
        return view('dashboard/index');
    }

    private function checkAdminAccess()
    {
        if (session()->get('role') != 'admin') {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Access denied');
        }
    }

    public function import()
    {
        $this->checkAdminAccess();
        // ...existing code for import
    }

    public function user()
    {
        $this->checkAdminAccess();
        // ...existing code for user
    }

    public function createUser()
    {
        $this->checkAdminAccess();
        // ...existing code for creating user
    }

    public function editUser($id = null)
    {
        $this->checkAdminAccess();
        // ...existing code for editing user
    }

    public function deleteUser($id = null)
    {
        $this->checkAdminAccess();
        // ...existing code for deleting user
    }
}
