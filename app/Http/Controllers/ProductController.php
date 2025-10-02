<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category_product;
use App\Models\Supplier;

use Illuminate\View\View;
//import return type redirect respons
use Illuminate\Http\RedirectResponse;
//import facades storage
use Illuminate\Support\Facades\Storage;

use Illuminate\Http\Request;
use Redirect;

class ProductController extends Controller
{
    /**
     * index
     * 
     * @return void
     */
    public function index(): View
    {
        $product = new Product();
        $products = $product->get_product()->latest()->paginate(10);

        return view('products.index', compact('products'));
    }

    /**
     * create
     * 
     * @return View
     */
    public function create(): View
    {
        $product = new Category_product;
        $data['categories'] = $product->get_category_product()->get();

        $supplier = new Supplier;
        $data['suppliers'] = $supplier->get_supplier()->get();

        return view('products.create', compact('data'));
    }

    /**
     * store
     * 
     * @param mixed $request
     * @return RedirectResponse
     */
    public function store(request $request): RedirectResponse
    {
        //var_dump($request);exit;
        //validate form
        $validatedData = $request->validate([
            'image'=>                   'required|image|mimes:jpeg,jpg,png|max:10240',
            'title'=>                   'required|min:5',
            'supplier'=>                'required|integer',
            'product_category_id'=>     'required|integer',
            'description'=>             'required|min:10',
            'price'=>                   'required|numeric',
            'stock'=>                   'required|numeric',
        ]);

        //Menghandle updload file gambar
        if ($request->hasFile('image')){
            $image = $request->file('image');
            $store_image = $image->store('image','public'); //simpan gambar ke folder penyimpanan

            $product = new Product;
            $insert_product = $product->storeProduct($request, $image);

            //redirect to index
            return redirect()->route('products.index')->with(['succes' => 'Data Berhasil Disimpan!']);
        }
        
        //return to index
        return redirect()->route('products.index')->with(['error' => 'Failed to upload image (request).']);
    }

    /**
     * show
     * 
     * @param mixed $id
     * @return View
     */
    public function show(string $id): View
    {
        //product by id
        $product_model = new Product();
        $product = $product_model->get_product()->where('products.id', $id)->firstOrFail();
        return view('products.show', compact('product'));
    }

    /**
     * edit
     * 
     * @param mixed $id
     * @return View
     */
    public function edit(string $id): View
    {
        $product_model = new Product();
        $data['product'] = $product_model->get_product()->where('products.id', $id)->firstOrFail();

        $category_product_model = new Category_product;
        $product['categories'] = $category_product_model->get_category_product()->get();

        $supplier = new Supplier;
        $product['suppliers'] = $supplier->get_supplier()->get();

        $merge_data = array_merge($data, $product);

        //render view product edit
        return view('products.edit', compact('merge_data'));
    }

    /**
     * update
     * 
     * @param mixed $request
     * @param mixed $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id): RedirectResponse
    {
        //validate form
        $request->validate([
            'title'=>   'required|min:5',
            'supplier'=>                'required|integer',
            'product_category_id'=>     'required|integer',
            'description'=>             'required|min:10',
            'price'=>                   'required|numeric',
            'stock'=>                   'required|numeric'
        ]);

        //get product by id
        $product_model = new Product;
        $name_image = null;

        //check if image is uploaded
        if ($request->hasFile('image')){
            //upload new image
            $image = $request->file('image');
            $store_image = $image->store('images', 'public');
            $name_image = $image->hashName();

            //cari data berdasarkan id
            $data_product = $product_model->get_product()->where("products.id", $id)->firstOrFail();
            //delete old image
            Storage::disk('public')->delete('image/'.$data_product->image);
        }

            //update product with new image
            $request= [
                'title' => $request->title,
                'supplier_id' => $request->supplier,
                'product_category_id' => $request->product_category_id,
                'description' => $request->description,
                'price' => $request->price,
                'stock' => $request->stock
            ];

            $update_product = $product_model->updateProduct($id, $request, $name_image);
            return redirect()->route('products.index')->with(['succes'=> 'Data berhasil diubah']);
        
    }

    /**
     * destroy
     * 
     * @param mixed $id
     * @return RedirectResponse
     */
    public function destroy(string $id): RedirectResponse{
        $product_model = new Product;
        $product = $product_model->get_product()->where('products.id', $id)->firstOrFail();

        Storage::disk('public')->delete('image/'.$product->image);

        $product->delete();
        return redirect()->route('products.index')->with(['success' => 'Data Berhasil Dihapus!']);
    }
}