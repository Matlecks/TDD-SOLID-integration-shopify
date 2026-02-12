<?php

namespace App\Domains\Shopify\Actions;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ShowInstallPageAction extends Controller
{
    /**
     * Displays the Shopify app installation page.
     *
     * @return View
     */
    public function __invoke(): View
    {
        return view('shopify.install');
    }
}
