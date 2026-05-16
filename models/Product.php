<?php

class Product
{
    private $conn;
    private $table_name = "products";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getConnection()
    {
        return $this->conn;
    }

    public function readAll()
    {
        $query = "SELECT 
                    p.sku, p.product_name AS name, c.category_name AS cat, 
                    p.price, p.stock_quantity AS stock, p.image_url,
                    p.brand, p.price_out, p.unit, p.location, p.mfg_date, p.exp_date, p.description,
                    p.specification
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.is_deleted = 0
                  ORDER BY p.sku DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function create($data)
    {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET product_name = :name, sku = :sku, category_id = :category_id, brand = :brand,
                      price = :price_in, price_out = :price_out, stock_quantity = :stock, 
                      min_stock = :min_alert, unit = :unit, location = :location, 
                      mfg_date = :mfg_date, exp_date = :exp_date, image_url = :image_url, description = :description,
                      specification = :specification, is_deleted = 0";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":name", $data['name']);
        $stmt->bindParam(":sku", $data['sku']);
        $stmt->bindParam(":category_id", $data['category_id'], PDO::PARAM_INT);
        $stmt->bindParam(":brand", $data['brand']);
        $stmt->bindParam(":price_in", $data['price_in']);
        $stmt->bindParam(":price_out", $data['price_out']);
        $stmt->bindParam(":stock", $data['stock'], PDO::PARAM_INT);
        $stmt->bindParam(":min_alert", $data['min_alert'], PDO::PARAM_INT);
        $stmt->bindParam(":unit", $data['unit']);
        $stmt->bindParam(":location", $data['location']);
        $stmt->bindParam(":mfg_date", $data['mfg_date'], $data['mfg_date'] ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindParam(":exp_date", $data['exp_date'], $data['exp_date'] ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindParam(":image_url", $data['image_url']);
        $stmt->bindParam(":description", $data['description']);
        $stmt->bindParam(":specification", $data['specification']);

        return $stmt->execute();
    }

    public function update($data)
    {
        $this->conn->beginTransaction();

        try {
            $sqlOld = "SELECT product_name, stock_quantity, price, price_out, specification FROM " . $this->table_name . " WHERE sku = :sku AND is_deleted = 0";
            $stmtOld = $this->conn->prepare($sqlOld);
            $stmtOld->bindParam(":sku", $data['sku']);
            $stmtOld->execute();
            $oldProduct = $stmtOld->fetch(PDO::FETCH_ASSOC);

            if (!$oldProduct) {
                $this->conn->rollBack();
                return false;
            }

            $query = "UPDATE " . $this->table_name . " 
                      SET product_name = :name, category_id = :category_id, brand = :brand,
                          price = :price_in, price_out = :price_out, stock_quantity = :stock, 
                          min_stock = :min_alert, unit = :unit, location = :location, 
                          mfg_date = :mfg_date, exp_date = :exp_date, image_url = :image_url, description = :description,
                          specification = :specification
                      WHERE sku = :sku AND is_deleted = 0";

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(":sku", $data['sku']);
            $stmt->bindParam(":name", $data['name']);
            $stmt->bindParam(":category_id", $data['category_id'], PDO::PARAM_INT);
            $stmt->bindParam(":brand", $data['brand']);
            $stmt->bindParam(":price_in", $data['price_in']);
            $stmt->bindParam(":price_out", $data['price_out']);
            $stmt->bindParam(":stock", $data['stock'], PDO::PARAM_INT);
            $stmt->bindParam(":min_alert", $data['min_alert'], PDO::PARAM_INT);
            $stmt->bindParam(":unit", $data['unit']);
            $stmt->bindParam(":location", $data['location']);
            $stmt->bindParam(":mfg_date", $data['mfg_date'], $data['mfg_date'] ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindParam(":exp_date", $data['exp_date'], $data['exp_date'] ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindParam(":image_url", $data['image_url']);
            $stmt->bindParam(":description", $data['description']);
            $stmt->bindParam(":specification", $data['specification']);

            if (!$stmt->execute()) {
                $this->conn->rollBack();
                return false;
            }

            $changes = [];
            if ($oldProduct['product_name'] !== $data['name']) {
                $changes[] = "Đổi tên: '" . $oldProduct['product_name'] . "' -> '" . $data['name'] . "'";
            }
            if ((int)$oldProduct['stock_quantity'] !== (int)$data['stock']) {
                $changes[] = "Thay đổi tồn kho: " . $oldProduct['stock_quantity'] . " -> " . $data['stock'];
            }
            if ((float)$oldProduct['price'] !== (float)$data['price_in']) {
                $changes[] = "Sửa giá nhập: " . number_format($oldProduct['price']) . "đ -> " . number_format($data['price_in']) . "đ";
            }
            if ((float)$oldProduct['price_out'] !== (float)$data['price_out']) {
                $changes[] = "Sửa giá bán: " . number_format($oldProduct['price_out']) . "đ -> " . number_format($data['price_out']) . "đ";
            }
            if ($oldProduct['specification'] !== $data['specification']) {
                $changes[] = "Sửa quy cách: '" . $oldProduct['specification'] . "' -> '" . $data['specification'] . "'";
            }

            $logDetails = !empty($changes) ? implode(" | ", $changes) : "Cập nhật thông tin mô tả/hình ảnh thuộc tính phụ.";

            $sqlLog = "INSERT INTO system_logs (user_id, action_module, action_type, description) 
                       VALUES (:user_id, :action_module, :action_type, :description)";

            $stmtLog = $this->conn->prepare($sqlLog);

            $moduleName = "Kho hàng";
            $actionType = "UPDATE";
            $fullDescription = "Cập nhật sản phẩm [SKU: " . $data['sku'] . "] -> " . $logDetails;

            $stmtLog->bindParam(":user_id", $data['user_id']);
            $stmtLog->bindParam(":action_module", $moduleName);
            $stmtLog->bindParam(":action_type", $actionType);
            $stmtLog->bindParam(":description", $fullDescription);
            $stmtLog->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function delete($sku, $user_id)
    {
        $this->conn->beginTransaction();
        try {
            $sqlCheck = "SELECT product_name FROM " . $this->table_name . " WHERE sku = :sku AND is_deleted = 0";
            $stmtCheck = $this->conn->prepare($sqlCheck);
            $stmtCheck->bindParam(":sku", $sku);
            $stmtCheck->execute();
            $product = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                $this->conn->rollBack();
                return false;
            }

            $query = "UPDATE " . $this->table_name . " SET is_deleted = 1 WHERE sku = :sku";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":sku", $sku);

            if (!$stmt->execute()) {
                $this->conn->rollBack();
                return false;
            }

            $sqlLog = "INSERT INTO system_logs (user_id, action_module, action_type, description) 
                       VALUES (:user_id, :action_module, :action_type, :description)";
            $stmtLog = $this->conn->prepare($sqlLog);

            $moduleName = "Kho hàng";
            $actionType = "DELETE";
            $fullDescription = "Xóa sản phẩm khỏi hệ thống kho [SKU: " . $sku . "] [Tên: " . $product['product_name'] . "]";

            $stmtLog->bindParam(":user_id", $user_id);
            $stmtLog->bindParam(":action_module", $moduleName);
            $stmtLog->bindParam(":action_type", $actionType);
            $stmtLog->bindParam(":description", $fullDescription);
            $stmtLog->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }
}