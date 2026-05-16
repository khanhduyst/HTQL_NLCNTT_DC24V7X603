<?php

require_once dirname(__DIR__) . '/models/Product.php';

class ProductController
{
    private $productModel;

    public function __construct($db)
    {
        $this->productModel = new Product($db);
    }

    public function handleGet()
    {
        $stmt = $this->productModel->readAll();
        $num = $stmt->rowCount();

        if ($num > 0) {
            $products_arr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $product_item = array(
                    "sku" => $sku,
                    "name" => $name,
                    "cat" => $cat,
                    "price" => number_format($price, 0, ',', '.'),
                    "price_out" => $price_out,
                    "stock" => $stock,
                    "image_url" => $image_url,
                    "brand" => $brand,
                    "unit" => $unit,
                    "location" => $location,
                    "mfg_date" => $mfg_date,
                    "exp_date" => $exp_date,
                    "description" => $description,
                    "specification" => $specification,
                    "status" => ($stock > 10) ? "instock" : (($stock > 0) ? "low" : "out"),
                    "label" => ($stock > 10) ? "Còn hàng" : (($stock > 0) ? "Sắp hết" : "Hết hàng"),
                    "color" => ($stock > 10) ? "#10b981" : (($stock > 0) ? "#f59e0b" : "#ef4444")
                );
                array_push($products_arr, $product_item);
            }
            echo json_encode(["status" => "success", "data" => $products_arr]);
        } else {
            echo json_encode(["status" => "success", "data" => []]);
        }
    }

    public function handlePost($data)
    {
        if (empty($data['sku']) || empty($data['name']) || empty($data['category_id'])) {
            echo json_encode(["status" => "error", "message" => "Thiếu thông tin bắt buộc!"]);
            return;
        }

        try {
            $cleanData = [
                'sku' => htmlspecialchars(strip_tags($data['sku'])),
                'name' => htmlspecialchars(strip_tags($data['name'])),
                'category_id' => (int)$data['category_id'],
                'brand' => htmlspecialchars(strip_tags($data['brand'] ?? '')),
                'price_in' => (float)($data['price_in'] ?? 0),
                'price_out' => (float)($data['price_out'] ?? 0),
                'stock' => (int)($data['stock'] ?? 0),
                'min_alert' => (int)($data['min_alert'] ?? 5),
                'unit' => htmlspecialchars(strip_tags($data['unit'] ?? 'Cái')),
                'location' => htmlspecialchars(strip_tags($data['location'] ?? '')),
                'mfg_date' => !empty($data['mfg_date']) ? $data['mfg_date'] : null,
                'exp_date' => !empty($data['exp_date']) ? $data['exp_date'] : null,
                'image_url' => htmlspecialchars(strip_tags($data['image_url'] ?? 'default.png')),
                'description' => htmlspecialchars(strip_tags($data['description'] ?? '')),
                'specification' => htmlspecialchars(strip_tags($data['specification'] ?? ''))
            ];

            if ($this->productModel->create($cleanData)) {
                echo json_encode(["status" => "success", "message" => "Thêm sản phẩm thành công!"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Không thể thêm sản phẩm."]);
            }
        } catch (Exception $e) {
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function handlePut($data)
    {
        if (empty($data['sku']) || empty($data['name']) || empty($data['category_id'])) {
            echo json_encode(["status" => "error", "message" => "Thiếu thông tin bắt buộc khi cập nhật!"]);
            return;
        }

        try {
            $cleanData = [
                'sku' => htmlspecialchars(strip_tags($data['sku'])),
                'name' => htmlspecialchars(strip_tags($data['name'])),
                'category_id' => (int)$data['category_id'],
                'brand' => htmlspecialchars(strip_tags($data['brand'] ?? '')),
                'price_in' => (float)($data['price_in'] ?? 0),
                'price_out' => (float)($data['price_out'] ?? 0),
                'stock' => (int)($data['stock'] ?? 0),
                'min_alert' => (int)($data['min_alert'] ?? 5),
                'unit' => htmlspecialchars(strip_tags($data['unit'] ?? 'Cái')),
                'location' => htmlspecialchars(strip_tags($data['location'] ?? '')),
                'mfg_date' => !empty($data['mfg_date']) ? $data['mfg_date'] : null,
                'exp_date' => !empty($data['exp_date']) ? $data['exp_date'] : null,
                'image_url' => htmlspecialchars(strip_tags($data['image_url'] ?? 'default.png')),
                'description' => htmlspecialchars(strip_tags($data['description'] ?? '')),
                'specification' => htmlspecialchars(strip_tags($data['specification'] ?? '')),
                'user_id' => (int)($_SESSION['user_id'] ?? 1)
            ];

            if ($this->productModel->update($cleanData)) {
                echo json_encode(["status" => "success", "message" => "Cập nhật sản phẩm và lưu nhật ký thành công!"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Không thể cập nhật sản phẩm."]);
            }
        } catch (Exception $e) {
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function handleDelete($data)
    {
        if (empty($data['sku'])) {
            echo json_encode(["status" => "error", "message" => "Thiếu mã sản phẩm cần xóa!"]);
            return;
        }

        try {
            $sku = htmlspecialchars(strip_tags($data['sku']));
            $user_id = (int)($_SESSION['user_id'] ?? 1);

            if ($this->productModel->delete($sku, $user_id)) {
                echo json_encode(["status" => "success", "message" => "Đã xóa sản phẩm khỏi hệ thống kho thành công!"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Không thể xóa sản phẩm hoặc sản phẩm không tồn tại."]);
            }
        } catch (Exception $e) {
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    }
}