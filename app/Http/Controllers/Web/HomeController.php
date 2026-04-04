<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Contracts\Repositories\BranchRepositoryInterface;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class HomeController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected BranchRepositoryInterface $branchRepo,
    ) {}
    
    public function index()
    {
        $branches = $this->branchRepo->getActive();
        return view('pages.landing.index', compact(
            'branches'
        ));
    }
}
