<?php

namespace App\Controllers;

use App\Models\Court;

class HomeController extends BaseController
{
    public function index($id = null)
    {
        $courts = (new Court())->getFeaturedCourts();
        $this->render('client/home', compact('courts'));
    }

    public function courts($id = null)
    {
        $keyword = trim($_GET['keyword'] ?? '');
        $filter_price = $_GET['filter_price'] ?? '';
        $filter_status = $_GET['filter_status'] ?? '';
        $courts = (new Court())->searchCourts($keyword, $filter_price, $filter_status);
        $this->render('client/courts', compact('keyword', 'filter_price', 'filter_status', 'courts'));
    }

    public function detail($id = null)
    {
        $courtId = (int) ($id ?? $_GET['id'] ?? 0);
        $court = (new Court())->getCourtById($courtId);
        if (!$court) {
            header('Location: ' . APP_BASE_URL . '/home/courts');
            exit;
        }
        $this->render('client/court-detail', compact('court'));
    }
}
