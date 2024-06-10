<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Hamcrest\Core\Set;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:management-access');
    }

    public function index()
    {
        $title = 'Setting';
        $breadcrumbs = ['Setting'];
        $setting = Setting::first() ?? new Setting();

        return view('setting.index', compact('title', 'breadcrumbs', 'setting'));
    }

    function store(Request $request)
    {
        $attr = $request->validate([
            'name' => 'required|string',
            'ucapan' => 'required|string',
            'deskripsi' => 'required|string',
            'logo' => 'nullable|mimes:jpg,jpeg,png,gif',
        ]);

        try {
            DB::beginTransaction();

            $setting = Setting::first();
            $logo = $request->file('logo');
            $logoUrl = '';

            $attr['use_logo'] = $request->use_logo == 'on' ? 1 : 0;

            if ($setting) {
                if ($request->file('logo')) {
                    Storage::delete($setting->logo);
                    $logoUrl = $logo->storeAs('logo', date('ymdhis') . rand(100, 990) . '.' . $logo->extension());
                } else {
                    $logoUrl = $setting->logo;
                }

                $attr["logo"] = $logoUrl;
                $setting->update($attr);
            } else {
                $attr["logo"] = $logo->storeAs('logo', date('ymdhis') . rand(100, 990) . '.' . $logo->extension());

                Setting::create($attr);
            }

            DB::commit();

            return back()->with('success', "Seting successfully saved");
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->with('error', $th->getMessage());
        }
    }
}
