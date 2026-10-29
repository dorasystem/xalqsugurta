<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;

/** Branches and offices (/{locale}/branches) */
final class BranchController extends Controller
{
    public function index(string $locale): View
    {
        try {
            $branches = Branch::active()->get();
        } catch (QueryException) {
            $branches = collect();   // not migrated yet
        }

        return view('pages.branches.index', ['branches' => $branches, 'locale' => $locale]);
    }
}
