<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PolicyController extends Controller
{
    public function show()
    {
        return view('privacy-policy');  // Create this view
    }
} 