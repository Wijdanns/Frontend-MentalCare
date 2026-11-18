<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DummyController extends Controller
{
    public function index()
    {
        $pesan = "Halo, ini versi update";

        return $pesan;
    }
}
