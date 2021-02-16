<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Purchases_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function addExpense($data = [])
    {
        if ($this->db->insert('expenses', $data)) {
            return true;
        }
        return false;
    }

    public function addPurchase($data, $items)
    {
        if ($this->db->insert('purchases', $data)) {
            $purchase_id = $this->db->insert_id();
            foreach ($items as $item) {
                $item['purchase_id'] = $purchase_id;
                if ($this->db->insert('purchase_items', $item)) {
                    if ($data['received']) {
                        $this->setStoreQuantity($item['product_id'], $data['store_id'], $item['quantity']);
                    }
                }
            }
            return true;
        }
        return false;
    }

    public function deleteExpense($id)
    {
        if ($this->db->delete('expenses', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function deletePurchase($id)
    {
        $purchase = $this->getPurchaseByID($id);
        if ($purchase->received) {
            $oitems = $this->getAllPurchaseItems($id);
            foreach ($oitems as $oitem) {
                if ($product = $this->site->getProductByID($oitem->product_id)) {
                    $this->setStoreQuantity($oitem->product_id, $purchase->store_id, (0 - $oitem->quantity));
                }
            }
        }
        if ($this->db->delete('purchases', ['id' => $id]) && $this->db->delete('purchase_items', ['purchase_id' => $id])) {
            return true;
        }
        return false;
    }

    public function getAllPurchaseItems($purchase_id)
    {
        $this->db->select('purchase_items.*, products.code as product_code, products.name as product_name')
            ->join('products', 'products.id=purchase_items.product_id', 'left')
            ->group_by('purchase_items.id')
            ->order_by('id', 'asc');
        $q = $this->db->get_where('purchase_items', ['purchase_id' => $purchase_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getExpenseByID($id)
    {
        $q = $this->db->get_where('expenses', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductByID($id)
    {
        $q = $this->db->get_where('products', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductNames($term, $limit = 10, $strict = false)
    {
        if ($strict) {
            $this->db->where('code', $term);
        } else {
            if ($this->db->dbdriver == 'sqlite3') {
                $this->db->where("type != 'combo' AND (name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR  (name || ' (' || code || ')') LIKE '%" . $term . "%')");
            } else {
                $this->db->where("type != 'combo' AND (name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR  concat(name, ' (', code, ')') LIKE '%" . $term . "%')");
            }
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

    public function getPurchaseByID($id)
    {
        $q = $this->db->get_where('purchases', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getStoreQuantity($product_id, $store_id)
    {
        $q = $this->db->get_where('product_store_qty', ['product_id' => $product_id, 'store_id' => $store_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function setStoreQuantity($product_id, $store_id, $quantity)
    {
        if ($store_qty = $this->getStoreQuantity($product_id, $store_id)) {
            $this->db->update('product_store_qty', ['quantity' => ($store_qty->quantity + $quantity)], ['product_id' => $product_id, 'store_id' => $store_id]);
        } else {
            $this->db->insert('product_store_qty', ['product_id' => $product_id, 'store_id' => $store_id, 'quantity' => $quantity]);
        }
    }

    public function updateExpense($id, $data = [])
    {
        if ($this->db->update('expenses', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function updatePurchase($id, $data = null, $items = [])
    {
        $purchase = $this->getPurchaseByID($id);
        if ($purchase->received) {
            $oitems = $this->getAllPurchaseItems($id);
            foreach ($oitems as $oitem) {
                if ($product = $this->site->getProductByID($oitem->product_id)) {
                    $this->setStoreQuantity($oitem->product_id, $purchase->store_id, (0 - $oitem->quantity));
                }
            }
        }
        if ($this->db->update('purchases', $data, ['id' => $id]) && $this->db->delete('purchase_items', ['purchase_id' => $id])) {
            foreach ($items as $item) {
                $item['purchase_id'] = $id;
                if ($this->db->insert('purchase_items', $item)) {
                    if ($data['received'] && $product = $this->site->getProductByID($item['product_id'])) {
                        $this->setStoreQuantity($item['product_id'], $purchase->store_id, $item['quantity']);
                    }
                }
            }
            return true;
        }
        return false;
    }
}
