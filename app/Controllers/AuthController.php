<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\ActivityLogModel;

class AuthController extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function login()
    {
        if (session()->get('logged_in')) {
            return redirect()->to('/dashboard');
        }

        $data = [
            'title' => 'Login'
        ];

        return view('auth/login', $data);
    }

    public function authenticate()
    {
        $rules = [
            'login'    => 'required',
            'password' => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $login = $this->request->getPost('login', FILTER_SANITIZE_STRING);
        $password = $this->request->getPost('password', FILTER_SANITIZE_STRING);

        if (empty($login) || empty($password) || strlen($login) > 100 || strlen($password) > 100) {
            // Log gagal login
            $logModel = new ActivityLogModel();
            $logModel->insert([
                'user_id' => null,
                'action' => 'login_failed',
                'description' => 'Login gagal untuk: ' . $login
            ]);
            return redirect()->back()->withInput()->with('error', 'Login or password is invalid');
        }
        $user = $this->userModel->findUserByEmailOrUsername($login);

        if (!$user || !password_verify($password, $user['password'])) {
            // Log gagal login
            $logModel = new ActivityLogModel();
            $logModel->insert([
                'user_id' => $user['id'] ?? null,
                'action' => 'login_failed',
                'description' => 'Login gagal untuk: ' . $login
            ]);
            return redirect()->back()->withInput()->with('error', 'Invalid credentials');
        }

        // Log berhasil login
        $logModel = new ActivityLogModel();
        $logModel->insert([
            'user_id' => $user['id'],
            'action' => 'login_success',
            'description' => 'Login berhasil untuk: ' . $user['username']
        ]);

        // Set session data
        session()->set([
            'user_id'    => $user['id'],
            'username'   => $user['username'],
            'email'      => $user['email'],
            'role'       => $user['role'],
            'logged_in'  => true
        ]);

        return redirect()->to('/dashboard')->with('success', 'Login successful');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login')->with('success', 'Logout successful');
    }
}
