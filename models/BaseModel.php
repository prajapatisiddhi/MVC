<?php
require_once 'models/Database.php';

class BaseModel{
    protected $conn;
    protected $tableName;
    protected $headerList;
    protected $fields; 
    protected $limit; 
    protected $defaultSort; 
    protected $defaultOrder; 

    public function __construct( $tableName) {
        $db = new Database();
        $this ->conn = $db->conn;
        $this->tableName = $tableName;
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->tableName} WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    public function save($data, $id = null) {

        $cols = array_keys($data); 
        $vals = array_values($data); 
        $set = implode(', ', array_map(fn($col) => "$col = ?", $cols));  

        if ($id) {
            // update
            $sql = "UPDATE {$this->tableName} SET $set WHERE id = ?";
            $types = str_repeat('s', count($vals)) . 'i'; 
            $vals[] = $id;
        } else {
            $types = str_repeat('s', count($vals)); 
            $sql = "INSERT INTO {$this->tableName} SET $set"; 
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$vals); 
        return $stmt->execute();
    }

   public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM {$this->tableName} WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }


    public function getList() { 
        

        $limit = $this->getLimit();
        
        $search = $this->getSearch();

        $sort = $_GET['sort'] ?? $this->defaultSort;
        $order = strtoupper($_GET['order'] ?? $this->defaultOrder); 
        $page = max(1, intval($_GET['page'] ?? 1)); 
        $offset = ($page - 1) * $limit; 

          if (!in_array($sort, $this->getAllowedSort())) {
            $sort = 'id'; 
        }


        $order = ($order === 'DESC') ? 'DESC' : 'ASC'; 
        $where = "";
        $params = [];
        $types = "";

        if ($search !== '') {
            $searchItem = "%$search%";
            $whereCols = $this->getSearchColumn();
            $where = "WHERE " . implode(" OR ", array_map(fn($col) => "$col LIKE ?", $whereCols));
            $params = array_fill(0, count($whereCols), $searchItem);
            $types = str_repeat('s', count($params));
        }

        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM {$this->tableName} {$where}");
        if ($where) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $totalrow = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
        $stmt->close();

        $totalPage = max(1, ceil($totalrow / $limit));
        $sql = "SELECT * FROM {$this->tableName} {$where} ORDER BY {$sort} {$order} LIMIT ? OFFSET ?";
        $stmt = $this->conn->prepare($sql);
        if ($where) {
            $stmt->bind_param($types . 'ii', ...array_merge($params, [$limit, $offset]));
        } else {
            $stmt->bind_param('ii', $limit, $offset);
        }
        $stmt->execute();
        $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        return compact('data', 'totalPage', 'page', 'search', 'sort', 'order', 'limit');
    }

  public function getAll() {
    $result = $this->getList();
    $data = $result['data'];

    foreach ($data as &$row) {
        foreach ($this->fields as $fieldName => $config) {
            if (!isset($row[$fieldName])) continue;

            // ✅ Checkbox (IDs stored as CSV)
            if ($config['type'] === 'checkbox' && !empty($row[$fieldName])) {
                $ids = explode(',', $row[$fieldName]);
                $labels = [];

                // अगर config में options पहले से है → use it
                if (!empty($config['options'])) {
                    foreach ($ids as $id) {
                        $id = trim($id);
                        $labels[] = $config['options'][$id] ?? $id;
                    }
                } 
                // नहीं है तो related model से निकालो (dynamic)
                else {
                    $relatedTable = $fieldName; // hobby/category/products जैसा
                    $stmt = $this->conn->prepare("SELECT id, name FROM {$relatedTable} WHERE id IN (" . implode(',', array_fill(0, count($ids), '?')) . ")");
                    $types = str_repeat('i', count($ids));
                    $stmt->bind_param($types, ...$ids);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    while ($r = $res->fetch_assoc()) {
                        $labels[] = $r['name'];
                    }
                }

                $row[$fieldName] = implode(', ', $labels);
            }

            // ✅ Radio (single ID → Label)
            if ($config['type'] === 'radio' && !empty($row[$fieldName])) {
                $val = $row[$fieldName];
                $row[$fieldName] = $config['options'][$val] ?? $val;
            }
        }

        // ✅ Special Case: Category → Products reverse relation
        if ($this->tableName === "category") {
            $catId = $row['id'];
            $prodnames = [];
            $stmt = $this->conn->prepare("SELECT name FROM product WHERE FIND_IN_SET(?, category)");
            $stmt->bind_param("i", $catId);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($r = $res->fetch_assoc()) {
                $prodnames[] = $r['name'];
            }
            $row['products'] = implode(", ", $prodnames);
        }
    }

    $result['data'] = $data;
    return $result;
}


    public function getConnection(){
        return $this->conn;
    }

    public function Header() {
        $headers = [];
        foreach ($this->headerList as $col) {
            if ($col === 'id') {
                $headers['id'] = 'ID';
                continue;
            }
            $label = $this->fields[$col]['label'] ?? ucfirst(str_replace('_',' ', $col));
            $headers[$col] = $label;
        }
        return $headers;
    }
    
    public function getLimit() {
        
        $module = $this->tableName;

        if (isset($_GET['limit'])) {
            $_SESSION[$module]['limit'] = (int)$_GET['limit'];
        }

        if (!isset($_SESSION[$module]['limit'])) {
            $_SESSION[$module]['limit'] = $this->limit; 

        }
    
        $limit = $_SESSION[$module]['limit'];
    
        $options = $this->limitOptions;

        if (!in_array($limit, $options)) {
            $limit = $this->limit;
            $_SESSION[$module]['limit'] = $limit;
        }
    
        return $limit;
    }

    public function getSearch() {
    $searchKey = $this->tableName . "_search";

    if (isset($_GET[$searchKey])) {
        $_SESSION[$searchKey] = trim($_GET[$searchKey]);
    }

    if (!isset($_SESSION[$searchKey])) {
        $_SESSION[$searchKey] = '';
    }

    return $_SESSION[$searchKey];
}



}
