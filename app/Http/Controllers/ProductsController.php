<?php

namespace Plac\Http\Controllers;

use Illuminate\Http\Request;
use Plac\Product;
use Plac\Product_Categories;
use Plac\ProductUserView;
use Carbon\Carbon;
use DB;
use Plac\Helpers\HelperIDs;
use Plac\Http\Controllers\NotificationStoreController;

class ProductsController extends Controller {

    //Get products by store with filter place
    public function getProductsByStore(Request $request) {
        $filters = $request->filters;
        $place_id = $filters['place_id'];
        $search = $filters['search'];
        $products = Product::where("product_stock", ">", 0)
                ->where("product_images", "!=", "[]")
                ->where("product_state", "=", 'ACTIVE')
                ->where('place_id', $place_id);
        $products = ($search!= '') ? $this->getSearchContinue($products, $search) : $products;
        $products = $products->paginate(9);
        $products = $this->getProductsColumnsDecode($products);

        return $products;
    }

    public function getProductsMain2(Request $request) {
        $filter = $request->filters;
        $productType = $filter['productType'];
        $cityId = $filter['cityId'];
        $value = $filter['value'];
        $petType = $filter['petType'];
        $categories = $filter['categories'];

        $products = Product::where("product_stock", ">", 0);

        if ($productType != '') {
            $products = $products->where("product_type", $productType);
        }
        $products = $products->where("product_images", "!=", "[]")->where("product_state", "=", 'ACTIVE');

        $products = ($value != '') ? $this->getSearchContinue($products, $value) : $products;
        $products = (count($categories) > 0) ? $this->getCategoriesContinue($products, $categories) : $products;
        $products = ($petType != '') ? $this->getPetTypesContinue($products, $petType) : $products;
        $products = ($cityId != '') ? $this->getCityContinue($products, $cityId) : $products;

        $products = $products->with("placeLocation.place")
                ->paginate(15);
        $products = $this->getProductCategories($products);
        $products = $this->getProductsColumnsDecode($products);
        return $products;
    }

    public function getSearchContinue($products, $value) {
        $products->where(function ($query) use ($value) {
            $query->orWhereRaw("lower(product_name) like '%$value%'")
                    ->orWhereRaw("lower(product_description) like '%$value%' ");
        });

        return $products;
    }

    public function getCategoriesContinue($products, $categories) {
        for ($i = 0; $i < count($categories); $i++) {
            $categoryId = $categories[$i]['category_id'];
            $products->where("product_categories", "like", "%$categoryId%");
        }
        return $products;
    }

    public function getPetTypesContinue($products, $petType) {
        $products->where("product_target", "like", "%$petType%");
        return $products;
    }

    public function getCityContinue($products, $cityId) {
        $products->where(function ($query)use ($cityId) {
            $query->where("product_delivery", "like", "%$cityId%")
                    ->orWhere("product_delivery", "like", '%"national":true%');
        });

        return $products;
    }

    public function getProductCategories($products) {
        $category_product = Product_Categories::get();
        $countStore = 0;
        foreach ($products as $product) {
            $listCategory = array();
            $count = 0;
            foreach ($category_product as $categoryProduct) {
                $categories_product = json_decode($product->product_categories);
                foreach ($categories_product as $category) {
                    if ($categoryProduct->category_id == $category) {
                        $listCategory[$count] = $categoryProduct->category_name;
                        $count++;
                    }
                }
            }
            $products[$countStore]->product_categories = implode(", ", $listCategory);
            $countStore++;
        }
        return $products;
    }

    public function getCategories($productType) {

        $products = Product_Categories::where("product_type", $productType)
                ->orderBy("category_name", "asc")
                ->get();
        for ($i = 0; $i < count($products); $i++) {
            $products[$i]["isSelected"] = 0;
            $products[$i]["position"] = $i;
        }
        return $products;
    }

    /* The products that are views for the users */

    public function addViewToProduct(Request $request) {

        $carbon = Carbon::now();
        $productUserViews = new ProductUserView();
        $productUserViews->product_user_views_id = $this->generateUniqueIdProductUserView();
        $productUserViews->product_id = $request->product_id;
        $productUserViews->plac_user_id = $request->plac_user_id;
        $productUserViews->created_at = $carbon;

        $productUserViews->save();
        return "view_added";
    }

    /* Generate a unique at the table product_users_views */

    public function generateUniqueIdProductUserView() {
        $idGenerated = HelperIDs::generateID();
        $count_exist_id_product = ProductUserView::where('product_user_views_id', $idGenerated)->count();

        if ($count_exist_id_product == 1) {
            $this->generateUniqueIdProductUserView();
        } else {
            return $idGenerated;
        }
    }

    public static function updateProductStock($productId, $quantity) {
        $product = \Plac\Product::find($productId);
        $productStock = (int) $product->product_stock;
        $product->product_stock = $productStock - $quantity;
        $product->save();
    }

    public function getProductsColumnsDecode($products) {

        $i = 0;
        foreach ($products as $product) {
            $product->product_images = json_decode($product->product_images);
            $product->product_categories = json_decode($product->product_categories);
            $product->product_delivery = json_decode($product->product_delivery);
            $products[$i] = $product;
            $i++;
        }
        return $products;
    }

    public function checkStock($quantityOrder, $productOrdered) {
        $productStock = $productOrdered['product_stock'];
        $placeId = $productOrdered['place_location']['place_location_id'];
        $check = true;

        if ($quantityOrder > $productStock) {
            $check = false;
        }
        if ($productStock == 1 || ($productStock - $quantityOrder) == 0) {
            /* ------ Save notification ------ */
            $url = \Plac\Helpers\Environment::getServerNameMainCurrentEnvironment() . "productos/" . $productOrdered['product_id'];
            $type = "stockzero";
            $title = "Inventario en cero";
            $content = "El inventario del producto " . $productOrdered['product_name'] . " ha llegado a cero.";
//            NotificationStoreController::saveNotification($url, $type, $title, $content, $placeId);
//            NotificationStoreController::sendNotificationToPlace($placeId, $url, 'El stock de su producto ha llegado a cero.');
            /* Send of email for the company by the stock of a productOrdered in zero */
        }
        return $check;
    }

    public function getProduct($productId) {

        $products = Product::where('product_id', $productId)->with('placeLocation.place')->get();
        if (count($products) == 0) {
            abort(404);
        }
        $products = $this->getProductsColumnsDecode($products);
        return $products;
    }

}
