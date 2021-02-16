<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Products_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function add_products($data = [])
    {
        if ($this->db->insert_batch('products', $data)) {
            return true;
        }
        return false;
    }

    public function addProduct($data, $store_quantities, $items = [])
    {
        if ($this->db->insert('products', $data)) {
            $product_id = $this->db->insert_id();
            if (!empty($store_quantities)) {
                foreach ($store_quantities as $store_quantity) {
                    $store_quantity['product_id'] = $product_id;
                    $this->db->insert('product_store_qty', $store_quantity);
                }
            }
            if (!empty($items)) {
                foreach ($items as $item) {
                    $item['product_id'] = $product_id;
                    $this->db->insert('combo_items', $item);
                }
            }
            return true;
        }
        return false;
    }

    public function deleteProduct($id)
    {
        if ($this->db->delete('products', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function fetch_products($limit, $start = null, $category_id = null)
    {
        $this->db->select('name, code, barcode_symbology, price')
        ->limit($limit, $start)->order_by('code', 'asc');
        if ($category_id) {
            $this->db->where('category_id', $category_id);
        }
        $q = $this->db->get('products');

        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllProducts()
    {
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getComboItemsByPID($product_id)
    {
        $this->db->select($this->db->dbprefix('products') . '.id as id, ' . $this->db->dbprefix('products') . '.code as code, ' . $this->db->dbprefix('combo_items') . '.quantity as qty, ' . $this->db->dbprefix('products') . '.name as name')
        ->join('products', 'products.code=combo_items.item_code', 'left')
        ->group_by('combo_items.id');
        $q = $this->db->get_where('combo_items', ['product_id' => $product_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getProductByCode($code)
    {
        $q = $this->db->get_where('products', ['code' => $code], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductNames($term, $limit = 10)
    {
        if ($this->db->dbdriver == 'sqlite3') {
            $this->db->where("type != 'combo' AND (name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR  (name || ' (' || code || ')') LIKE '%" . $term . "%')");
        } else {
            $this->db->where("type != 'combo' AND (name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR  concat(name, ' (', code, ')') LIKE '%" . $term . "%')");
        }
        $this->db->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getStoreQuantity($product_id, $store_id = null)
    {
        if (!$store_id) {
            $store_id = $this->session->userdata('store_id') ? $this->session->userdata('store_id') : 1;
        }
        $q = $this->db->get_where('product_store_qty', ['product_id' => $product_id, 'store_id' => $store_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getStoresQuantity($product_id)
    {
        $q = $this->db->get_where('product_store_qty', ['product_id' => $product_id]);
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function products_count($category_id = null)
    {
        if ($category_id) {
            $this->db->where('category_id', $category_id);
            return $this->db->count_all_results('products');
        }
        return $this->db->count_all('products');
    }

    public function setStoreQuantity($data)
    {
        if ($this->getStoreQuantity($data['product_id'], $data['store_id'])) {
            $this->db->update('product_store_qty', ['quantity' => $data['quantity'], 'price' => $data['price']], ['product_id' => $data['product_id'], 'store_id' => $data['store_id']]);
        } else {
            $this->db->insert('product_store_qty', $data);
        }
    }

    public function updatePrice($data = [])
    {
        if ($this->db->update_batch('products', $data, 'code')) {
            return true;
        }
        return false;
    }

    public function updateProduct($id, $data = [], $store_quantities = [], $items = [], $photo = null)
    {
        if ($photo) {
            $data['image'] = $photo;
        }
        if ($this->db->update('products', $data, ['id' => $id])) {
            if (!empty($store_quantities)) {
                foreach ($store_quantities as $store_quantity) {
                    $store_quantity['product_id'] = $id;
                    $this->setStoreQuantity($store_quantity);
                }
            }
            if (!empty($items)) {
                $this->db->delete('combo_items', ['product_id' => $id]);
                foreach ($items as $item) {
                    $item['product_id'] = $id;
                    $this->db->insert('combo_items', $item);
                }
            }
            return true;
        }
        return false;
    }
}
