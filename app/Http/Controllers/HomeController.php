<?php

namespace App\Http\Controllers;

use App\Http\Resources\FeatureResource;
use App\Models\Feature;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;

class HomeController extends Controller
{
    public function index()
    {
        return Inertia::render('Welcome', [
            'login' => Route::has('login'),
            'register' => Route::has('register'),
            'logout' => Route::has('logout'),
        ]);
    }
}


// Removed duplicate and commented-out class definition for clarity.