<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CustomController extends Controller
{
    public function show()
    {
        // Your logic here, e.g., returning a view or some data
        return view('relays-page');
    }
}
