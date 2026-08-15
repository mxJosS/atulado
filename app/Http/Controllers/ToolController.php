<?php

namespace App\Http\Controllers;

use App\Models\CrisisLine;
use App\Models\Resource;
use Illuminate\Http\Request;

class ToolController extends Controller
{
    public function sientes()
    {
        $featuredResources = Resource::where('is_featured', true)->take(6)->get();
        return view('sientes', compact('featuredResources'));
    }

    public function respiracion()
    {
        return view('tools.respiracion');
    }

    public function grounding()
    {
        return view('tools.grounding');
    }

    public function stop()
    {
        return view('tools.stop');
    }

    public function crisis(Request $request)
    {
        $query = CrisisLine::query();

        if ($request->filled('country') && $request->country !== 'todos') {
            $query->where('country_code', $request->country);
        }

        $crisisLines = $query->orderBy('order_index')->get();
        $countries = CrisisLine::select('country', 'country_code')->distinct()->get();

        return view('crisis', compact('crisisLines', 'countries'));
    }
}
