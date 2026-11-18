<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DummyController extends Controller
{
    public function index()
    {
        $text = "Haii, ini aku";

        return $text;
    }
}
