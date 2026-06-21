<?php

namespace App\Http\Controllers;

class LegalController extends Controller
{
    public function privacidade()
    {
        return view('legal.politica-privacidade');
    }

    public function termos()
    {
        return view('legal.termos-de-uso');
    }
}
