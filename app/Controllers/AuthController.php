<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\ActivityLogModel;
use CodeIgniter\Cache\CacheInterface;

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

    private function getRealIp()
    {
        $ip = $this->request->getIPAddress();
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $forwarded = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($forwarded[0]);
        } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        if ($ip === '::1' || $ip === '127.0.0.1') {
            $ip = 'localhost';
        }
        return $ip;
    }

    public function authenticate()
    {
        $cache = \Config\Services::cache();
        $ip = $this->getRealIp();
        $login = $this->request->getPost('login', FILTER_SANITIZE_STRING);
        $cacheKey = 'login_attempt_' . md5($ip . '_' . strtolower($login));
        $lockKey = 'login_lock_' . md5($ip . '_' . strtolower($login));
        $maxAttempts = 5;
        $lockDuration = 60; // 10 menit
        $delayBase = 2; // detik

        // Cek lockout
        if ($cache->get($lockKey)) {
            $ttl = $cache->getMetadata($lockKey)['expire'] - time();
            return redirect()->back()->withInput()->with('lockout',$ttl);
        }

        // Hitung percobaan login
        $attempts = $cache->get($cacheKey) ?? 0;
        // Adaptive delay
        if ($attempts > 0) {
            sleep($delayBase * $attempts);
        }

        $rules = [
            'login'    => 'required',
            'password' => 'required'
        ];

        if (!$this->validate($rules)) {
            $attempts++;
            $cache->save($cacheKey, $attempts, $lockDuration);
            if ($attempts >= $maxAttempts) {
                $cache->save($lockKey, true, $lockDuration);
                return redirect()->back()->withInput()->with('error', 'Terlalu banyak percobaan gagal. Silakan coba lagi nanti.');
            }
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $password = $this->request->getPost('password', FILTER_SANITIZE_STRING);

        if (empty($login) || empty($password) || strlen($login) > 100 || strlen($password) > 100) {
            // Log gagal login
            $logModel = new ActivityLogModel();
            $logModel->insert([
                'user_id' => null,
                'action' => 'login_failed',
                'description' => 'Login gagal untuk: ' . $login . ' | IP: ' . $ip
            ]);
            $attempts++;
            $cache->save($cacheKey, $attempts, $lockDuration);
            if ($attempts >= $maxAttempts) {
                $cache->save($lockKey, true, $lockDuration);
                return redirect()->back()->withInput()->with('error', 'Terlalu banyak percobaan gagal. Silakan coba lagi nanti.');
            }
            return redirect()->back()->withInput()->with('error', 'Login or password is invalid');
        }
        $user = $this->userModel->findUserByEmailOrUsername($login);

        if (!$user || !password_verify($password, $user['password'])) {
            // Log gagal login
            $logModel = new ActivityLogModel();
            $logModel->insert([
                'user_id' => $user['id'] ?? null,
                'action' => 'login_failed',
                'description' => 'Login gagal untuk: ' . $login . ' | IP: ' . $ip
            ]);
            $attempts++;
            $cache->save($cacheKey, $attempts, $lockDuration);
            if ($attempts >= $maxAttempts) {
                $cache->save($lockKey, true, $lockDuration);
                return redirect()->back()->withInput()->with('error', 'Terlalu banyak percobaan gagal. Silakan coba lagi nanti.');
            }
            return redirect()->back()->withInput()->with('error', 'Invalid credentials');
        }

        // Login berhasil, reset counter
        $cache->delete($cacheKey);
        $cache->delete($lockKey);

        // Log berhasil login
        $logModel = new ActivityLogModel();
        $logModel->insert([
            'user_id' => $user['id'],
            'action' => 'login_success',
            'description' => 'Login berhasil untuk: ' . $user['username'] . ' | IP: ' . $ip
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
