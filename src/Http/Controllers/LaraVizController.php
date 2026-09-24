<?php

namespace LaraViz\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;

class LaraVizController extends Controller
{
    public function __invoke(Request $request)
    {
        if (! Gate::check('viewLaraViz')) {
            abort(403, 'Unauthorized to access LaraViz.');
        }

        return view('laraviz::app');
    }
}
