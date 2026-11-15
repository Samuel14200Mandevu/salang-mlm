<?php
// app/Http/Controllers/CommissionDashboardController.php

namespace App\Http\Controllers;

class CommissionDashboardController extends Controller
{
    public function index()
    {
        return redirect()->route('commissions.index');
    }
}
