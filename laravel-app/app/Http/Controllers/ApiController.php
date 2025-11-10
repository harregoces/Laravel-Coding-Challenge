<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function today(Request $request)
    {
        return response()->json([
            'date' => now()->toDateString(),
        ]);
    }
}
