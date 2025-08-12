<?php

namespace App\Controllers\Dashboard;

use App\Controllers\BaseController;
use App\Models\UserModel;

class User extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        // Check if user is admin
        $redirect = $this->requireAdmin();
        if ($redirect) {
            return $redirect;
        }

        $data = [
            'title' => 'Management User',
            'users' => $this->userModel->findAll()
        ];

        return view('dashboard/user/index', $data);
    }

    public function create()
    {
        // Check if user is admin
        $redirect = $this->requireAdmin();
        if ($redirect) {
            return $redirect;
        }

        $data = [
            'title' => 'Add New User'
        ];

        return view('dashboard/user/create', $data);
    }

    public function store()
    {
        // Check if user is admin
        $redirect = $this->requireAdmin();
        if ($redirect) {
            return $redirect;
        }

        $validation = \Config\Services::validation();

        $rules = [
            'username' => 'required|min_length[3]|max_length[50]|is_unique[users.username]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[6]',
            'role' => 'required|in_list[admin,user]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $data = [
            'username' => $this->request->getPost('username'),
            'email' => $this->request->getPost('email'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'role' => $this->request->getPost('role'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        if ($this->userModel->insert($data)) {
            return redirect()->to('dashboard/user')->with('success', 'User berhasil ditambahkan');
        } else {
            return redirect()->back()->with('error', 'Gagal menambahkan user');
        }
    }

    public function edit($id)
    {
        // Check if user is admin
        $redirect = $this->requireAdmin();
        if ($redirect) {
            return $redirect;
        }

        $user = $this->userModel->find($id);

        if (!$user) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('User tidak ditemukan');
        }

        $data = [
            'title' => 'Edit User',
            'user' => $user
        ];

        return view('dashboard/user/edit', $data);
    }

    public function update($id)
    {
        // Check if user is admin
        $redirect = $this->requireAdmin();
        if ($redirect) {
            return $redirect;
        }

        $user = $this->userModel->find($id);

        if (!$user) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('User tidak ditemukan');
        }

        $validation = \Config\Services::validation();

        $rules = [
            'username' => "required|min_length[3]|max_length[50]|is_unique[users.username,id,$id]",
            'email' => "required|valid_email|is_unique[users.email,id,$id]",
            'role' => 'required|in_list[admin,user]'
        ];

        // Add password validation only if password is provided
        if ($this->request->getPost('password')) {
            $rules['password'] = 'min_length[6]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $data = [
            'username' => $this->request->getPost('username'),
            'email' => $this->request->getPost('email'),
            'role' => $this->request->getPost('role'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Update password only if provided
        if ($this->request->getPost('password')) {
            $data['password'] = password_hash($this->request->getPost('password'), PASSWORD_DEFAULT);
        }

        if ($this->userModel->update($id, $data)) {
            return redirect()->to('dashboard/user')->with('success', 'User berhasil diupdate');
        } else {
            return redirect()->back()->with('error', 'Gagal mengupdate user');
        }
    }

    public function delete($id)
    {
        // Check if user is admin
        $redirect = $this->requireAdmin();
        if ($redirect) {
            return $redirect;
        }

        $user = $this->userModel->find($id);

        if (!$user) {
            return redirect()->to('dashboard/user')->with('error', 'User tidak ditemukan');
        }

        if ($this->userModel->delete($id)) {
            return redirect()->to('dashboard/user')->with('success', 'User berhasil dihapus');
        } else {
            return redirect()->to('dashboard/user')->with('error', 'Gagal menghapus user');
        }
    }
}
