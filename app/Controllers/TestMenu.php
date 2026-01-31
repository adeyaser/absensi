<?php
namespace App\Controllers;
use App\Models\MenuModel;
class TestMenu extends BaseController {
    public function index() {
        $model = new MenuModel();
        $menus = $model->findAll();
        foreach ($menus as $m) {
            echo "ID: " . $m['id'] . " - " . $m['title'] . "\n";
        }
    }
}
