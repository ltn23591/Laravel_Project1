<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image; // Đảm bảo bạn đã import Facade của v3
use Illuminate\Support\Facades\File; // Import File facade nếu dùng hàm kiểm tra thư mục
class AdminController extends Controller
{
    public function index()
    {
        return view('admin.index');
    }

    public function brands()
    {
        $brands = Brand::orderBy('id', 'desc')->paginate(10);
        return view('admin.brands', compact('brands'));
    }

    public function add_brand()
    {
        return view('admin.add_brand');
    }

    public function store_brand(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|unique:brands,slug',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $brand = new Brand();
        $brand->name = $request->name;
        $brand->slug = Str::slug($request->name);
        $image = $request->file('image');
        $file_extension = $request->file('image')->extension(); //extension: jpg, png, jpeg, gif, svg
        $file_name = Carbon::now()->timestamp . '.' . $file_extension;
        $brand->image = $file_name;
        $this->GenerateBrandThumbailsImage($image, $file_name);
        $brand->save();
        return redirect()->route('admin.brands')->with('success', 'Brand has been added successfully');
    }
    public function GenerateBrandThumbailsImage($image, $imageName)
    {
        $destinationPath = public_path('uploads/brands/');

        // Mẹo nhỏ: Đảm bảo thư mục tồn tại để hàm save() không bị lỗi
        if (!File::isDirectory($destinationPath)) {
            File::makeDirectory($destinationPath, 0777, true, true);
        }

        // Đọc ảnh và xử lý bằng cú pháp v3
        Image::read($image->path())
            ->cover(124, 124)
            ->save($destinationPath . '/' . $imageName);
    }
}
