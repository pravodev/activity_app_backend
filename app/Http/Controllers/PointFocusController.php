<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PointFocus;

class PointFocusController extends Controller
{
    public function delete($id)
    {
        $pointFocus = PointFocus::findOrFail($id);
        $pointFocus->delete();

        $response = ['error' => false, 'data' => $pointFocus];
        return response()->json($response);
    }
}
