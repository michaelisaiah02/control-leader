<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class ChecksheetController extends Controller
{
    public function index(): View
    {
        return view("checksheet.index");
    }

    public function wizard_index(Request $request): View
    {
        $type = $request->type;
        $steps = [
            [
                'id' => 'step-1-absent',
                'label' => 'Nama Operator Pengganti',
                'name' => 'nama_pengganti',
                'type' => 'text',
                'placeholder' => 'Nama Lengkap',
            ],
            [
                'id' => 'step-2-absent',
                'label' => 'Bagian Operator Pengganti',
                'name' => 'bagian_pengganti',
                'type' => 'text',
                'placeholder' => 'Contoh: Finishing',
            ],
            [
                'id' => 'step-3-absent',
                'label' => 'Kondisi Operator Pengganti',
                'name' => 'kondisi_pengganti',
                'type' => 'radio',
                'options' => ['sehat', 'sakit'],
            ],
            [
                'id' => 'step-1-present',
                'label' => 'Kondisi Operator',
                'name' => 'kondisi_operator',
                'type' => 'radio',
                'options' => ['sehat', 'sakit'],
            ]
        ];

        return view("checksheet.wizard_index", compact(['steps', 'type']));
    }

    public function create(): View
    {
        return view("control_leader.input.add");
    }

    public function store(Request $request) {}

    public function edit($id): View
    {
        $checksheet = null;
        return view("control_leader.input.edit", compact(['checksheet']));
    }

    public function update(Request $request, $id)
    {
        return redirect()->route('checksheet.index');
    }
}
