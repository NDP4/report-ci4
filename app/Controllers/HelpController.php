<?php

namespace App\Controllers;

class HelpController extends BaseController
{
    public function documentation()
    {
        $data = [
            'title' => 'Dokumentasi & Bantuan',
            'description' => 'Panduan penggunaan aplikasi website Telkomsel Infomedia'
        ];
        return view('help/documentation', $data);
    }
}
